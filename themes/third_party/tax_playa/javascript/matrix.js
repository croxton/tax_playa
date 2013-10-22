(function($) {
	Matrix.bind('tax_playa', 'display', function(cell){

		var $field = $('.checkboxTree', this).find(" > ul");

		if ( $field.data("checkboxTree") == undefined) {

			$field.find(".root").addClass("collapsed"); // stop widget closing root branches

			$field.checkboxTree({  
	            initializeChecked: 'expanded',
	            initializeUnchecked: 'collapsed',
	            checkChildren: false,
	            checkParents: false,
	            onCheck: {
	                ancestors: '', 
	                descendants: '', 
	                node: ''
	            },
	            onUncheck: {
	                ancestors: '',
	                descendants: '',
	                node: ''
	            }   
	        });

	        // expand root branches
	        $field.data('checkboxTree').expand($field.find(".root"));
           
            // show li after tree has expanded
            setTimeout(function(){
                $field.find('li').css('visibility', 'visible');
            }, 500);
		}
	});
})(jQuery);
