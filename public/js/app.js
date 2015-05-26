$(function(){
	/* Group Admin Page */

	/* Searchable Dropdown Box */
	$("form.add-user-form").children("select").combobox();

	/* Deleting Users AJAX */
	$("form.user-action-form").submit(function(e){
		e.preventDefault();
		$.ajax({
			method: "post",
			url: $(this).attr("action"),
			context: $(this).closest("tr"),
			data: $(this).serialize(),
			cache: false
		}).done(function(data){
			if(data.status == "ok") {
				this.fadeOut("slow",function(){
					$("form.add-user-form select").append(
						$("<option></option>")
							.val($(this).children(".user-id").text())
							.html($(this).children(".char-name").text())
					);
					$("form.add-user-form select").combobox('refresh');
					$(this).remove();
				});
			}
		});
	});
});