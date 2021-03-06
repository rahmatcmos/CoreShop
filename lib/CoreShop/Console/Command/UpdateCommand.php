<?php
/**
 * CoreShop.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright  Copyright (c) 2015-2017 Dominik Pfaffenbauer (https://www.pfaffenbauer.at)
 * @license    https://www.coreshop.org/license     GNU General Public License version 3 (GPLv3)
 */

namespace CoreShop\Console\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Tool\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Pimcore\Tool\Admin;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use CoreShop\Update;

/**
 * Class UpdateCommand
 * @package CoreShop\Console\Command
 */
class UpdateCommand extends AbstractCommand
{
    /**
     * configure command.
     */
    protected function configure()
    {
        $this
            ->setName('coreshop:update')
            ->setDescription('Update CoreShop to the desired version/build')
            ->addOption(
                'list', 'l',
                InputOption::VALUE_NONE,
                'List available updates'
            )
            ->addOption(
                'update', 'u',
                InputOption::VALUE_OPTIONAL,
                'Update to the given number / build'
            )
            ->addOption(
                'source-build', null,
                InputOption::VALUE_OPTIONAL,
                'specify a source build where the update should start from - this is mainly for debugging purposes'
            )->addOption(
                'dry-run', 'd',
                InputOption::VALUE_NONE,
                'Dry-run'
            );
    }

    /**
     * execute command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dryRun = $input->getOption('dry-run');
        $currentRevision = null;

        if ($dryRun) {
            $this->output->writeln('<info>---------- DRY-RUN ----------</info>');
        }

        if ($input->getOption('source-build')) {
            $currentRevision = $input->getOption('source-build');
        }

        Update::$dryRun = $dryRun;

        $availableUpdates = Update::getAvailableUpdates($currentRevision);

        if ($input->getOption('list')) {
            if (count($availableUpdates['releases'])) {
                $rows = [];
                foreach ($availableUpdates['releases'] as $release) {
                    $rows[] = [$release['version'], date('Y-m-d', $release['date']), $release['id']];
                }

                $table = new Table($output);
                $table
                    ->setHeaders(['Version', 'Date', 'Build'])
                    ->setRows($rows);
                $table->render();
            }

            if (count($availableUpdates['revisions'])) {
                $latest = count($availableUpdates['revisions']) - 1;

                $this->output->writeln('The latest available build is: <comment>'.$availableUpdates['revisions'][$latest]['number'].'</comment> ('.date('Y-m-d', $availableUpdates['revisions'][$latest]['timestamp']).')');
            }

            if (!count($availableUpdates['releases']) && !count($availableUpdates['revisions'])) {
                $this->output->writeln('<info>No updates available</info>');
            }
        }

        if ($input->getOption('update')) {
            $returnMessages = [];
            $build = null;
            $return = "";

            $updateInfo = trim($input->getOption('update'));
            if (is_numeric($updateInfo)) {
                $build = $updateInfo;
            } else {
                // get build nr. by version number
                foreach ($availableUpdates['releases'] as $release) {
                    if ($release['version'] == $updateInfo) {
                        $build = $release['id'];
                        break;
                    }
                }
            }

            if (!$build) {
                $this->writeError('Update with build / version '.$updateInfo.' not found.');
                exit;
            }

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion("You are going to update to build $build! Continue with this action? (y/n)", false);

            if (!$helper->ask($input, $output, $question)) {
                return;
            }

            $this->output->writeln('Starting the update process ...');

            $packages = Update::getPackages($build, $currentRevision);

            $progress = new ProgressBar($output, count($packages));
            $progress->start();

            foreach ($packages['parallel'] as $package) {
                Update::downloadPackage($package['revision'], $package['url']);
                $progress->advance();
            }

            $progress->finish();

            $jobs = Update::getJobs($build, $currentRevision);
            $steps = count($jobs['parallel']) + count($jobs['procedural']);

            $progress = new ProgressBar($output, $steps);
            $progress->start();

            foreach ($jobs['parallel'] as $job) {
                if ($job['type'] == 'arrange') {
                    Update::arrangeData($job['revision'], $job['url'], $job['file']);
                }

                $progress->advance();
            }

            $maintenanceModeId = 'cache-warming-dummy-session-id';
            Admin::activateMaintenanceMode($maintenanceModeId);

            $stoppedByError = false;
            foreach ($jobs['procedural'] as $job) {
                $phpCli = Console::getPhpCli();

                if ($dryRun) {
                    $job['dry-run'] = true;
                }

                $cmd = $phpCli.' '.realpath(PIMCORE_PATH.DIRECTORY_SEPARATOR.'cli'.DIRECTORY_SEPARATOR.'console.php').' coreshop:internal:update-processor '.escapeshellarg(json_encode($job));
                $return = Console::exec($cmd);

                $return = trim($return);

                $returnData = @json_decode($return, true);
                if (is_array($returnData)) {
                    if (trim($returnData['message'])) {
                        $returnMessages[] = [$job['revision'], strip_tags($returnData['message'])];
                    }

                    if (!$returnData['success']) {
                        $stoppedByError = true;
                        break;
                    }
                } else {
                    $stoppedByError = true;
                    break;
                }

                $progress->advance();
            }

            $progress->finish();

            Admin::deactivateMaintenanceMode();

            $this->output->writeln("\n");

            if ($stoppedByError) {
                $this->output->writeln('<error>Update stopped by error! Please check your logs</error>');
                $this->output->writeln('Last return value was: '.$return);
            } else {
                $this->output->writeln('<info>Update done!</info>');

                if (count($returnMessages)) {
                    $table = new Table($output);
                    $table
                        ->setHeaders(['Build', 'Message'])
                        ->setRows($returnMessages);
                    $table->render();
                }
            }
        }
    }
}
