<?php
/*
  use \Glial\Synapse\FactoryController;

  echo '<div class="well">';
  echo '<div class="btn-group" role="group" aria-label="Default button group">';


  foreach ($data['link'] as $link) {

  echo '<a href="' . LINK . $link['url'] . '" type="button" class="btn btn-primary" style="font-size:12px">'
  . ' <span class="glyphicon ' . $link['icon'] . '" aria-hidden="true"></span> ' . $link['name'] . '</a>';
  }


  echo '</div>';

  echo '</div>';

  FactoryController::addNode("Home", "list_server", array());
 */
?>


<div style="background:#fff; width:100%" class="container">
    <div class="row">
        <ul class="padnull">
            <?php
            foreach ($data['item'] as $item) {



                echo '<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 Services-tab item">
                    <div class="folded-corner service_tab_1">
                        <div class="text">
                            <div class="row">
                                <div class="col-md-1"> <i class="icon-puzzle-piece"></i>

                            '.$item['icon'].'</div>
                                <div class="col-md-7">
                                    <p class="item-title">
                                    <h3> '.$item['title'].'</h3>
                                    </p><!-- /.item-title -->
                                </div>

<div class="col-md-3" style="text-align:right">';


                if (!empty($item['button_url'])) {
                    echo '<a href="'.str_replace('{LINK}', LINK, $item['button_url']).'" class="btn btn-primary">'.$item['button_msg'].'</a>';
                }


                echo '</div><div class="col-md-1"></div>

                            </div>
                            <p><br /></p>
                            <p>
                                ';

                \Glial\Synapse\FactoryController::addNode($item['class'], $item['method'], array());
                echo $item['class'].", ".$item['method'];
                echo '  </p>
                        </div>
                    </div>
                </div>';
            }
            ?>

        </ul>
    </div>
</div>



<?php

/*

<div style="background:#fff; width:100%" class="container">
    <div class="row">
        <ul class="padnull">
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab  item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="icon-puzzle-piece"></i>

                        <i class="fa fa-puzzle-piece fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Plugins</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-database fa-5x fa-icon-image" ></i>
                        <p class="item-title">
                        <h3> Developing</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-trash fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Cleaner</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-comments fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Alert</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-line-chart fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3>Analytics</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-cube  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3>Rescue</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-credit-card fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> e-commerce</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-cloud-upload  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Support</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-cloud-upload  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Support</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-cloud-upload  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Support</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-cloud-upload  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Support</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 Services-tab item">
                <div class="folded-corner service_tab_1">
                    <div class="text">
                        <i class="fa fa-sitemap  fa-5x fa-icon-image"></i>
                        <p class="item-title">
                        <h3> Master / Slave</h3>
                        </p><!-- /.item-title -->
                        <p>
                            This is an amazing set of animated accordions based completely on CSS. They come oriented both vertically and horizontally in order to fit properly in your project. In order to see the slides,
                        </p>
                    </div>
                </div>
            </div>
        </ul>
    </div>
</div>


*/
