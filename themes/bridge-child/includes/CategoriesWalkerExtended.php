<?php

// Categories walker extended for taxanomy archive sidebar
class Walker_Categories_Template extends Walker_Category {

    public $level;
    
    function start_lvl(&$output, $depth = 1, $args = array()) {
        Global $level;
        $level++;

        $output .= "\n<div class=\"panel list-sub\">\n";
        $output .= "\n<div class=\"panel-body\">\n";
        $output .= "\n<div class=\"list-group\">\n";
    }

    function end_lvl(&$output, $depth = 0, $args = array()) {
        Global $level;
        $level--;

        $output .= "</div>\n";
        $output .= "</div>\n";
        $output .= "</div>\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) {
        Global $level;
        if ( empty($level) || $level<1 ) {
            $level = 1;
        } elseif ( $level > 5 ) {
            $level = 5;
        }

        extract($args);

        $cat_name = esc_attr($item->name);
        $cat_name = apply_filters('list_cats', $cat_name, $item);
        /* $link = 'href="' . esc_url( get_term_link($item) ) . '" '; */
        $link = 'href="" ';
        if ($use_desc_for_title == 0 || empty($item->description))
            $link .= 'title="' . esc_attr(sprintf(__('View all posts filed under %s'), $cat_name)) . '"';
        else
            $link .= 'title="' . esc_attr(strip_tags(apply_filters('category_description', $item->description, $item))) . '"';

        if (!empty($show_count))
            $link .= ' (' . intval($item->count) . ')';
        if ('list' == $args['style']) {
            /* $output .= "\t<a"; */
            $output .= '<a id="' . $item->term_id . '"';
            $class = 'list-group-item cat-item-' . $item->term_id . ' child-' . $level;
            $symbol = '';

            $termchildren = get_term_children($item->term_id, $item->taxonomy);
            if (count($termchildren) > 0) {
                $class .= ' i-am-parent';
                $symbol = '<i class="fa fa-arrow-right pull-right"></i>';
            } else {
                $class .= ' i-am-child';
            }

            if (!empty($current_category)) {
                $_current_category = get_term($current_category, $item->taxonomy);
                if ($item->term_id == $current_category)
                    $class .= ' current-cat';
                elseif ($item->term_id == $_current_category->parent)
                    $class .= ' current-cat-parent';
            }
            $output .= ' class="' . $class . '"';
            $output .= "$link";
            $output .= ">" . $cat_name;
            $output .= $symbol . '</a>';
        } else {
            $output .= "$link";
            $output .= ">";
            $output .= $cat_name . '</a>';
        }
    }

}
