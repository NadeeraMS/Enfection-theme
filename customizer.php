<?php
/**
 * UnderStrap Theme Customizer
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
if ( ! function_exists( 'understrap_customize_register' ) ) {
    /**
     * Register basic customizer support.
     *
     * @param object $wp_customize Customizer reference.
     */
    function understrap_customize_register( $wp_customize ) {
        $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
        $wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
        $wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
    }
}
add_action( 'customize_register', 'understrap_customize_register' );

if ( ! function_exists( 'understrap_theme_customize_register' ) ) {
    /**
     * Register individual settings through customizer's API.
     *
     * @param WP_Customize_Manager $wp_customize Customizer reference.
     */
    function understrap_theme_customize_register( $wp_customize ) {

        // Theme layout settings.
        $wp_customize->add_section(
            'understrap_theme_layout_options',
            array(
                'title'       => __( 'Theme Layout Settings', 'understrap' ),
                'capability'  => 'edit_theme_options',
                'description' => __( 'Container width and sidebar defaults', 'understrap' ),
                'priority'    => apply_filters( 'understrap_theme_layout_options_priority', 160 ),
            )
        );

        /**
         * Select sanitization function
         *
         * @param string               $input   Slug to sanitize.
         * @param WP_Customize_Setting $setting Setting instance.
         * @return string Sanitized slug if it is a valid choice; otherwise, the setting default.
         */
        function understrap_theme_slug_sanitize_select( $input, $setting ) {

            // Ensure input is a slug (lowercase alphanumeric characters, dashes and underscores are allowed only).
            $input = sanitize_key( $input );

            // Get the list of possible select options.
            $choices = $setting->manager->get_control( $setting->id )->choices;

            // If the input is a valid key, return it; otherwise, return the default.
            return ( array_key_exists( $input, $choices ) ? $input : $setting->default );

        }

        $wp_customize->add_setting(
            'understrap_container_type',
            array(
                'default'           => 'container',
                'type'              => 'theme_mod',
                'sanitize_callback' => 'understrap_theme_slug_sanitize_select',
                'capability'        => 'edit_theme_options',
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'understrap_container_type',
                array(
                    'label'       => __( 'Container Width', 'understrap' ),
                    'description' => __( 'Choose between Bootstrap\'s container and container-fluid', 'understrap' ),
                    'section'     => 'understrap_theme_layout_options',
                    'settings'    => 'understrap_container_type',
                    'type'        => 'select',
                    'choices'     => array(
                        'container'       => __( 'Fixed width container', 'understrap' ),
                        'container-fluid' => __( 'Full width container', 'understrap' ),
                    ),
                    'priority'    => apply_filters( 'understrap_container_type_priority', 10 ),
                )
            )
        );

        $wp_customize->add_setting(
            'understrap_sidebar_position',
            array(
                'default'           => 'right',
                'type'              => 'theme_mod',
                'sanitize_callback' => 'sanitize_text_field',
                'capability'        => 'edit_theme_options',
            )
        );

        $wp_customize->add_control(
            new WP_Customize_Control(
                $wp_customize,
                'understrap_sidebar_position',
                array(
                    'label'             => __( 'Sidebar Positioning', 'understrap' ),
                    'description'       => __(
                        'Set sidebar\'s default position. Can either be: right, left, both or none. Note: this can be overridden on individual pages.',
                        'understrap'
                    ),
                    'section'           => 'understrap_theme_layout_options',
                    'settings'          => 'understrap_sidebar_position',
                    'type'              => 'select',
                    'sanitize_callback' => 'understrap_theme_slug_sanitize_select',
                    'choices'           => array(
                        'right' => __( 'Right sidebar', 'understrap' ),
                        'left'  => __( 'Left sidebar', 'understrap' ),
                        'both'  => __( 'Left & Right sidebars', 'understrap' ),
                        'none'  => __( 'No sidebar', 'understrap' ),
                    ),
                    'priority'          => apply_filters( 'understrap_sidebar_position_priority', 20 ),
                )
            )
        );
    }
} // End of if function_exists( 'understrap_theme_customize_register' ).
add_action( 'customize_register', 'understrap_theme_customize_register' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
if ( ! function_exists( 'understrap_customize_preview_js' ) ) {
    /**
     * Setup JS integration for live previewing.
     */
    function understrap_customize_preview_js() {
        wp_enqueue_script(
            'understrap_customizer',
            get_template_directory_uri() . '/js/customizer.js',
            array( 'customize-preview' ),
            '20130508',
            true
        );
    }
}
add_action( 'customize_preview_init', 'understrap_customize_preview_js' );

// Start Enfectors page Team Section  
function shortcode_team_list($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'team',
            'numberposts' => 8,
            'order'=> 'ASC',
            'orderby' => 'date',
        ));
    ?>

    <div class="drop-down-section">
    <select class="form-select" id="enf" aria-label="Default select example" onchange="changeFunc();">
            <?php
            $categories = get_custom_categories( array(
                'order' => 'DESC'
            ), 'team_category');
            if(count($categories) > 0 ) :?>
                    <?php  foreach($categories as $types) : ?>
                        <option value="<?= $types->slug ?>">
                        <a class="btn" onclick="filterSelection('<?= $types->slug ?>');">
                            <?= $types->name ?>
                        </a>
                        </option>
                    <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <div class="row team-list">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
            <?php
            $terms = get_the_terms( $post->ID, 'team_category' );
            foreach($terms as $term) { ?>
                <div class="col-md-3 animate__animated animate__fadeInUp animate__slower wow fadeInUp filterDiv <?= $term->slug ?>">
                    <div>
                        <?php  $image = get_field('profile_pic', $post->ID );
                        if ( !empty( $image ) ) { ?>
                            <img src="<?php echo esc_url($image['url']); ?>" width="80%"  alt="<?= get_the_title($post->ID); ?>" />
                        <?php  } else { ?>
                            <img class="img-fluid" src="https://via.placeholder.com/300x300.png?text=No Image"
                                 alt="No Image - <?= get_the_title(); ?>"  />
                        <?php  } ?>
                    </div>
                    <div class="body-area">
                        <h4><?php echo get_the_title($post->ID); ?></h4>
                        <p><?php the_field('designation', $post->ID); ?></p>
                    </div>
                </div>
            <?php 			}	 ?>
        <?php endwhile; wp_reset_query(); ?>
    </div>
    <?php
}
add_shortcode( 'team_list', 'shortcode_team_list' );
// End Enfectors page Team Section

//epidomic months
function shortcode_epidemic($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'epidemic',
            'numberposts' => 12,
            'order'=> 'ASC',
            'orderby' => 'date',
        ));
    ?>

    <div class="owl-carousel owl-theme epidemic-carousel mt-5 pt-2 animate__animated animate__fadeInUp animate__slower wow fadeInUp">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
            <div class="item">
                <div class="slide">
                    <h4 class="w-100 text-center"><?php the_field('month', $post->ID); ?></h4>
                    <div class="card">
                        <div class="image-1 w-100 text-center">
                            <?php if ( has_post_thumbnail($post->ID) ) { ?>
                                <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" width="100%" />
                            <?php  } else { ?>
                                <img class="img-fluid" src="https://via.placeholder.com/600x400.png?text=No Image"
                                     alt="No Image - <?= get_the_title(); ?>" />
                            <?php  } ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold"><?php echo get_the_title($post->ID); ?></h5>
                            <p class="card-text mt-3"><?php the_field('short_description', $post->ID); ?></p>
                            <a target="_blank" href="<?php the_field('button_link', $post->ID); ?>" class="btn">Read More</a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endwhile; wp_reset_query(); ?>
    </div>
    <?php
}

add_shortcode( 'epidemic_months', 'shortcode_epidemic' );

//viral articles
function shortcode_viral($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'viral_article',
            'numberposts' => 20,
            'order'=> 'ASC',
            'orderby' => 'date',
        ));
    ?>

    <div class="owl-carousel owl-theme viral-carousel mt-5 pt-2">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
            <div class="item animate__animated animate__fadeInUp animate__slower wow fadeInUp">
                <div class="slide">
                    <div class="card">
                        <div class="image-1 w-100 text-center">
                            <?php if ( has_post_thumbnail($post->ID) ) { ?>
                                <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" width="100%" />
                            <?php  } else { ?>
                                <img class="img-fluid" src="https://via.placeholder.com/600x400.png?text=No Image"
                                     alt="No Image - <?= get_the_title(); ?>" />
                            <?php  } ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold"><?php echo get_the_title($post->ID); ?></h5>
                            <p class="card-text mt-3"><?php the_field('short_description', $post->ID); ?></p>
                            <a target="_blank" href="<?php the_field('viral_article', $post->ID); ?>" class="btn">Read More</a>
                        </div>
                    </div>

                </div>
            </div>
        <?php endwhile; wp_reset_query(); ?>
    </div>
    <?php
}

add_shortcode( 'viral_articles', 'shortcode_viral' );

function shortcode_enfectious($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'post',
//            'numberposts' => 8,
            'order'=> 'DES',
            'orderby' => 'date',
        ));
    ?>
    <div class="enfecter-cards">

        <div class="row">
            <div class="col-md-12 w-100 category-nav">
                <?php
                $categories = get_categories( array(
                    'order' => 'ASC'
                ));
                if(count($categories) > 0 ) :
                    ?>
                    <ul class="animate__animated animate__flipInX animate__slow wow flipInX">
                        <li>
                            <a class="btn catnav-btn all-btn" onclick="filterSelectionBlogPost('all'),filterSelectionBlogPostAll();">All</a>
                        </li>
                        <?php  foreach($categories as $types) : ?>
                            <li>
                                <a class="btn catnav-btn" onclick="filterSelectionBlogPost('<?= $types->slug ?>'),filterSelectionBlogPostFilter();"><?= $types->name ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div  id="enfectors_id"  class="row">
            <?php while ( $args->have_posts() ) : $args->the_post(); ?>
                <?php
                $terms = get_the_terms( $post->ID, 'category' );
                foreach($terms as $term) {
                    ?>
                    <div class="animate__animated animate__fadeInUp animate__slow wow fadeInUp col-lg-4 col-sm-12 col-md-6 mb-4 filterDivBlogPost reperter-wrapper <?= $term->slug ?>">
                        <div class="card">
                            <div class="image-1 w-100 text-center">
                                <?php if ( has_post_thumbnail($post->ID) ) { ?>
                                    <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" width="100%" />
                                <?php  } else { ?>
                                    <img class="img-fluid" src="https://via.placeholder.com/600x400.png?text=No Image"
                                         alt="No Image - <?= get_the_title(); ?>" />
                                <?php  } ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title font-weight-bold"><?php echo get_the_title($post->ID); ?></h5>
                                <p class="card-text mt-2"><?php the_field('short_description', $post->ID); ?></p>
                                <a href="<?php the_permalink($post->ID); ?>" class="btn btn-primary mt-2">Read More</a>
<!--                                <p>--><?php //echo $row['post_count']; ?><!--</p>-->
                            </div>
                        </div>
                    </div>
                <?php 			}	 ?>
            <?php endwhile; wp_reset_query(); ?>
        </div>

        <div  id="enfectors_id_two"  class="row">
            <?php while ( $args->have_posts() ) : $args->the_post(); ?>
                <?php
                $terms = get_the_terms( $post->ID, 'category' );
                foreach($terms as $term) {
                    ?>
                    <div class="col-lg-4 col-sm-12 col-md-6 mb-4 filterDivBlogPost <?= $term->slug ?>">
                        <div class="card">
                            <div class="image-1 w-100 text-center">
                                <?php if ( has_post_thumbnail($post->ID) ) { ?>
                                    <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" width="100%" />
                                <?php  } else { ?>
                                    <img class="img-fluid" src="https://via.placeholder.com/600x400.png?text=No Image"
                                         alt="No Image - <?= get_the_title(); ?>" />
                                <?php  } ?>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title font-weight-bold"><?php echo get_the_title($post->ID); ?></h5>
                                <p class="card-text mt-2"><?php the_field('short_description', $post->ID); ?></p>
                                <a href="<?php the_permalink($post->ID); ?>" class="btn btn-primary mt-2">Read More</a>
                            </div>
                        </div>
                    </div>
                <?php 			}	 ?>
            <?php endwhile; wp_reset_query(); ?>
        </div>

    </div>

    <?php
}
add_shortcode( 'enfectious_profiles', 'shortcode_enfectious' );

//shortcode of more videos
function shortcode_more_videos($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'more_video',
            'numberposts' => 5,
            'order'=> 'ASC',
            'orderby' => 'date',
            'post_status' => 'publish',
        ));
    ?>

    <div class="row">
        <div class="col-md-5 col-sm-12 text-area">
            <div class="left">

            <svg xmlns="http://www.w3.org/2000/svg" width="251" height="15" viewBox="0 0 251 15">
            <defs>
                <style>
                .cls-1 {
                    fill: none;
                    stroke: #88298a !important;
                    stroke-linecap: round;
                    stroke-width: 10px;
                }
                </style>
            </defs>
            <line id="Line_25" data-name="Line 25" class="cls-1" x2="236" transform="translate(7.5 7.5)"/>
            </svg>


            <div class="text">
            <h5 id="selected-name" class="card-title font-weight-bold">Sean Sim</h5>
            <p id="selected-designation" class="card-text mt-3">Chief Executive Officer</strong></p>
            <p id="selected-company" class="card-text mt-3">McCann Worldgroup Malaysia</p>
            </div>
            </div>
           
        </div>
        <div class="col-md-7 col-sm-12 video-area">
            <div class="embed-responsive embed-responsive-21by9">
            <iframe  id="mp4_src" class="embed-responsive-item" src="https://s3.ap-south-1.amazonaws.com/enfection.com-s3/videos/sean-seem.mp4" allowfullscreen></iframe>
            </div>
        </div>
    </div>

    <h5 class="mv-title">More Videos</h5>
    <hr>
    <div class="owl-carousel owl-theme more-videos-carousel mt-3 pt-2">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
            <div class="item animate__animated animate__fadeInUp animate__slower wow fadeInUp">
                <div id="<?php echo get_post_field('post_name',$post->ID);?>" class="slide">
                        <div class="card">
                            <div class="image-1 w-100 text-center">
                                <a class="btn" onclick="openVideoOnAction('<?php the_field('company', $post->ID); ?>', '<?php the_field('designation', $post->ID); ?>', '<?php echo get_the_title($post->ID); ?>', '<?php the_field('video', $post->ID); ?>','<?php echo get_post_field('post_name',$post->ID);?>')">
                                    <?php if ( has_post_thumbnail($post->ID) ) { ?>
                                        <img src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" width="100%" />
                                    <?php  } else { ?>
                                        <img class="img-fluid" src="https://via.placeholder.com/600x400.png?text=No Image"
                                             alt="No Image - <?= get_the_title(); ?>" />
                                    <?php  } ?>
                                </a>
                            </div>
                            <div class="card-body">
                                <h5 class="vdo-name card-title font-weight-bold"><?php echo get_the_title($post->ID); ?></h5>
                                <p class="vdo-designation card-text mt-3"><strong><?php the_field('designation', $post->ID); ?></strong></p>
                                <p class="vdo-company card-text mt-3"><?php the_field('company', $post->ID); ?></p>
                                <div class="active-line"></div>      
                            </div>
                        </div>

                </div>
            </div>
        <?php endwhile; wp_reset_query(); ?>
    </div>
    <?php
}
add_shortcode( 'more_videos', 'shortcode_more_videos' );

// Start Growth Stories  Section 
function shortcode_growth_story($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'growth-story',
            'numberposts' => 5,
            'order'=> 'DES',
            'orderby' => 'date',
            'post_status' => 'publish',
            'offset' => '1',
        ));
    ?>

    <div id="growth-story" class="container stoy-boxes">
    <div  class="row row-cols-1 row-cols-md-3 g-5 box-row">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
        <div class="col growth-box">
                <div class="card h-100">
                    <img src="<?php the_field('image', $post->ID); ?>" class="card-img-top" alt="...">
                    <div class="card-body">
                        <h6 class="card-title"><?php the_field('tittle');?></h6>
                        <p class="card-sub-title"><?php the_field('sub_tittle');?></p>
                        <div class="content">
                            <p class="card-content-title"><?php the_field('content_title');?></p>
                            <?php if( have_rows('content') ): ?>
                            <ul >
                                <?php while( have_rows('content') ): the_row(); ?>
                                <li>
                                <i class="fa fa-circle"></i><p class="card-content"><?php the_sub_field('content_s');?></p>
                                </li>
                                <?php endwhile; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php the_permalink($post->ID); ?>">Further Reading</a>
                    </div>
                </div>
            </div>
        <?php endwhile; wp_reset_query(); ?>
        </div>
    </div>

    
    <?php
}
add_shortcode( 'growth_story', 'shortcode_growth_story' );
// End Growth Stories  Section





// Start Vacancies  Section 
function shortcode_vacancy($atts) {
    $args = new wp_query(
        array(
            'post_type' => 'vacancy',
            'numberposts' => 12,
            'order'=> 'ASCE',
            'orderby' => 'date',
            'post_status' => 'publish',
        ));
    ?>



   

    <h5 class="main-title">Vacancies</h5><br>

    
    <div  class="row row-cols-1 row-cols-md-3 g-5 vacancy">
        <?php while ( $args->have_posts() ) : $args->the_post(); ?>
        <div class="col vacancy-box">
                <div class="card h-100">
                    
                    <div class="card-body">
                        <h6 class="card-title"><?php the_field('team_name');?></h6>
                        <h5 class="card-sub-title"><?php the_field('sub_title');?></h5><br>
                        <p class="card-paragraph"><?php the_field('paragraph');?></p>
                        <div class="card-footer">
                            <a href="#join"><?php the_field('button');?><i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                    
                </div>
            </div>
        <?php endwhile; wp_reset_query(); ?>
    </div>
    

    
    <?php
    
}
add_shortcode( 'vacancy', 'shortcode_vacancy' );
// End Vacancies  Section