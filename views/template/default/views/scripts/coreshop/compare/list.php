<div class="row">

    <div class="col-xs-12">

        <h2 class="main-heading2">
            <?=$this->translate("Compare")?>
        </h2>

        <?php if( $this->error === TRUE ) { ?>

            <div class="alert alert-info">
                <p><?=$this->message;?></p>
            </div>

        <?php } else { ?>

            <div class="row">

               <?php foreach( $this->compareProducts as $product ) { ?>

                <div class="col-xs-12 col-sm-4 compare-block">

                    <div class="block">

                        <?= $this->template("coreshop/compare/helper/product.php", array("product" => $product)); ?>

                    </div>

                </div>

                <?php } ?>

            </div>

        <?php } ?>

    </div>

</div>