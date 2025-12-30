<?php 
global $qode_options_proya;
$blog_hide_comments = "";
if (isset($qode_options_proya['blog_hide_comments'])) {
	$blog_hide_comments = $qode_options_proya['blog_hide_comments'];
}

$blog_hide_author = "";
if (isset($qode_options_proya['blog_hide_author'])) {
    $blog_hide_author = $qode_options_proya['blog_hide_author'];
}

$qode_like = "on";
if (isset($qode_options_proya['qode_like'])) {
	$qode_like = $qode_options_proya['qode_like'];
}
$document = get_post_meta( get_the_ID(), 'bridge_document_document_file', true );
$download = get_post_meta( get_the_ID(), 'bridge_document_document_download', true );
$youtube_video = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_youtube', true ) );
$gdrive = esc_url( get_post_meta( get_the_ID(), 'bridge_document_document_gdrive', true ) );
?>
<div id="post-<?php the_ID(); ?>" <?php post_class('panel panel-default'); ?>>
    <div class="panel-heading" role="tab" id="heading-<?php the_ID(); ?>">
      <h4 class="panel-title">
        <a class="tab-title" role="button" data-toggle="collapse" data-parent="#dococument-parent" href="#collapse-<?php the_ID(); ?>" aria-expanded="true" aria-controls="collapse-<?php the_ID(); ?>"><?php echo get_the_date('m/d/Y')." - ".get_the_title(); ?></a>
        <?php
        if (!empty($document)) { ?>
        <a class="download-doc" id="<?php the_ID(); ?>" rel="nofollow" href="<?php if (!empty($download)) : echo $download; else : echo $document; endif; ?>" target="_blank" download>Download</a>
        <?php } ?>
      </h4>
    </div>
    <div id="collapse-<?php the_ID(); ?>" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading-<?php the_ID(); ?>">
      <div class="panel-body">
        <?php
        the_content();
        if (!empty($document)) {
            //echo do_shortcode('[wonderplugin_pdf src="'.$document.'" width="100%" height="800px" style="border:0;"]');
            echo do_shortcode('[pdf-embedder url="'.$document.'" width="100%"]');
        }
        elseif (!empty($youtube_video))  { ?>
            <div class="video-container">
                <?php
                $video_id = explode('=', $youtube_video);                    
                if(strpos($video_id[1],"&")){
                    $explode_more = explode('&', $video_id[1]);
                    $video_id = $explode_more[0];
                } else {
                    $video_id = $video_id[1];
                }
                ?>
                <div class="yt-lazyload" data-id="<?php echo $video_id ?>" data-random=""></div>
            </div>
        <?php
        }
        elseif (!empty($gdrive)) {
            echo '<div class="video-container">';
            echo '<iframe src="'.$gdrive.'"></iframe>';
            echo '</div>';
        }
        ?>
      </div>
    </div>
</div>