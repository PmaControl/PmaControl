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
                                    <h3> '.__('').'</h3>
                                    </p><!-- /.item-title -->
                                </div>

<div class="col-md-3" style="text-align:right">';

                echo '</div><div class="col-md-1"></div>

                            </div>
                            <p><br /></p>
                            <p>
                                ';

                echo 'xfgdhfdg';
                //echo $item['class'].", ".$item['method'];
                echo '  </p>
                        </div>
                    </div>
                </div>';
            }




            echo '<div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 Services-tab item">
                    <div class="folded-corner service_tab_1">
                        <div class="text">
                            <div class="row">
                                <div class="col-md-1"> <i class="icon-puzzle-piece"></i>

                            '.$item['icon'].'</div>
                                <div class="col-md-7">
                                    <p class="item-title">
                                    <h3> '.__('Appearance settings').'</h3>
                                    </p><!-- /.item-title -->
                                </div>

<div class="col-md-3" style="text-align:right">';

            if (!empty($item['button_url'])) {

                echo '<a href="'.str_replace(array('{LINK}', '{WWW_ROOT}'), array(LINK, WWW_ROOT), $item['button_url']).'" class="btn btn-primary">'.$item['button_msg'].'</a>';
            }


            echo '</div><div class="col-md-1"></div>

                            </div>
                            <p><br /></p>
                            <p>
                                ';

            echo ' LanguageDocumentation
English
 Theme:
darkwolf
';
            //echo $item['class'].", ".$item['method'];
            echo '  </p>
                        </div>
                    </div>
                </div>';
            ?>
        </ul>
    </div>
</div>