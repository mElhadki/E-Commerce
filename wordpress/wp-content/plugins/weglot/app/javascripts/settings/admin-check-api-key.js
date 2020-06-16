const init_admin_button_preview = function () {
	const $ = jQuery

	const execute = () => {

		$("#api_key_private").blur(function() {
			var key = $(this).val();
			if( key.length === 0){
                $(".weglot-keyres").remove();
                $("#api_key_private").after('<span class="weglot-keyres weglot-nokkey"></span>');
                $("#wrap-weglot #submit").prop("disabled", true);
				return;
			}

			function validApiKey(response){

				$(".weglot-keyres").remove();
				$("#api_key_private").after(
					'<span class="weglot-keyres weglot-okkey"></span>'
				);

				$("#wrap-weglot #submit").prop(
					"disabled",
					false
				);

				const evt = new CustomEvent("weglotCheckApi", {
					detail: response
				});

				window.dispatchEvent(evt);
			}

			function unvalidApiKey(){
				$(".weglot-keyres").remove();
				$("#api_key_private").after('<span class="weglot-keyres weglot-nokkey"></span>');
				$("#wrap-weglot #submit").prop("disabled", true);
			}

			$(".weglot-keyres").remove();
			$("#api_key_private").after('<span class="weglot-keyres weglot-ckeckkey"></span>');

			$.ajax(
				{
					method: 'POST',
					url: ajaxurl,
					data : {
						action: 'get_user_info',
						api_key: key,
					},
					success: ({data, success}) => {
						$(".weglot-keyres").remove();
						if (success ){
							validApiKey(data)
						}
						else{
							unvalidApiKey()
						}

					}
				}
			).fail(function() {
				unvalidApiKey()
			});
		});
	}

	document.addEventListener('DOMContentLoaded', () => {
		execute();
	})
}

export default init_admin_button_preview;

