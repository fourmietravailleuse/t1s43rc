<script type="text/javascript">
// Add some settings to the field (fieldSettings can be found in the gravityforms
// forms.js file)
if (fieldSettings.ayah === undefined)
	fieldSettings["ayah"] = ".label_setting, .css_class_setting";

function ayahgf_add_field(name) {

	// Create the AYAH field
    var nextId = GetNextFieldId();
    var field = new Field(nextId, name);	
	field.label = "<?php _e("Complete The Game Below To Prove You're A Human!", "gravityforms"); ?>";

	var mysack = new sack("<?php echo admin_url("admin-ajax.php")?>?id=" + form.id);
    mysack.execute = 1;
    mysack.method = 'POST';
    mysack.setVar( "action", "rg_add_field" );
    mysack.setVar( "rg_add_field", "<?php echo wp_create_nonce("rg_add_field") ?>" );
    mysack.setVar( "field", jQuery.toJSON(field) );
    mysack.encVar( "cookie", document.cookie, false );
    mysack.onError = function() { alert('<?php echo esc_js(__("Ajax error while adding field", "gravityforms")) ?>' )};
    mysack.runAJAX();

    return true;
}
</script>