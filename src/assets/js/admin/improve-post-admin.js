import $ from 'jquery';

// On ferme toutes les metaboxes ACF
$('.acf-postbox').addClass('closed');

// On masque les metaboxes de taxonomies dans l'edition des posts (on les rajoutera ensuite dans des champs ACF)
$('[id^="tagsdiv-"').hide();

// On referme la metabox "Attributs de page" + "Revisions"
$('#pageparentdiv').addClass('closed');
$('#revisionsdiv').addClass('closed');
$('#wpseo_meta').addClass('closed');
