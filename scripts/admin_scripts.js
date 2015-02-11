var $ = jQuery.noConflict();

jQuery(document).ready(function(){
	var st_category = jQuery("#edit_st_category_value").val();//comma separated ids
	jQuery('#edit_st_category').val('');
	var category_array = st_category.split(",");
	jQuery.each(category_array, function( index, value ) {
		jQuery('#edit_st_category option[value=' + value + ']').attr('selected', true);
	});
});