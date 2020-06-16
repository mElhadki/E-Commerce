const init_admin_select = function(){

    const $ = jQuery
    const generate_destination_language = () => {
        return weglot_languages.available.filter(itm => {
            return itm.code !== weglot_languages.original
        });
    }


    let destination_selectize

    const load_destination_selectize = () => {
        destination_selectize = $(".weglot-select-destination")
			.selectize({
				delimiter: "|",
				persist: false,
				valueField: "code",
				labelField: "local",
				searchField: ["code", "english", "local"],
				sortField: [{ field: "english", direction: "asc" }],
				maxItems: weglot_languages.limit,
				plugins: ["remove_button", "drag_drop"],
				options: generate_destination_language(),
				render: {
					option: function(item, escape) {
						return (
							'<div class="weglot__choice__language">' +
							'<span class="weglot__choice__language--english">' +
							escape(item.english) +
							"</span>" +
							'<span class="weglot__choice__language--local">' +
							escape(item.local) +
							" [" +
							escape(item.code) +
							"]</span>" +
							"</div>"
						);
					}
				}
			})
			.on("change", (value) => {
				const code_languages = destination_selectize[0].selectize.getValue()
				const template = $("#li-button-tpl");

				if (template.length  === 0){
					return;
				}


				const is_fullname = $("#is_fullname").is(":checked")
				const with_name = $("#with_name").is(":checked")
				const with_flags = $("#with_flags").is(":checked")

				let classes = ''
				if (with_flags) {
					classes = "weglot-flags";
				}

				let new_dest_language = ''
				code_languages.forEach(element => {
					const language = weglot_languages.available.find(itm => itm.code === element);
					let label = ''
					if(with_name){
						if (is_fullname){
							label = language.local
						}
						else{
							label = element.toUpperCase()
						}
					}


					new_dest_language += template
						.html()
						.replace("{LABEL_LANGUAGE}", label)
						.replace(new RegExp("{CODE_LANGUAGE}", "g"), element)
						.replace("{CLASSES}", classes)


				});
				$(".country-selector ul").html(new_dest_language)
			});
    }

    const execute = () => {
		let work_original_language = $("#original_language").val()

		$("#original_language").on("change", function (e) {
			const old_original_language = work_original_language;
			const new_destination_option = work_original_language;
			work_original_language = e.target.value;
			destination_selectize[0].selectize.removeOption(work_original_language);

			const new_option = weglot_languages.available.find(itm => {
				return itm.code === new_destination_option
			});

			const new_original_option = weglot_languages.available.find(itm => {
				return itm.code === work_original_language;
			});

			destination_selectize[0].selectize.addOption(new_option);


			const is_fullname = $("#is_fullname").is(":checked")
			const with_name = $("#with_name").is(":checked")
			let label = ''
			if(with_name){
				label = is_fullname ? new_original_option.local : new_original_option.code.toUpperCase();
			}

			$(".wgcurrent.wg-li")
				.removeClass(old_original_language)
				.addClass(work_original_language)
				.attr("data-code-language", work_original_language)
				.find('span').html(label)


		});


        load_destination_selectize();

        window.addEventListener("weglotCheckApi", (data) => {
            let limit = 1000
            const plan = data.detail.plan

            if (
                plan <= 0 ||
                weglot_languages.plans.starter_free.ids.indexOf(plan) >= 0
            ) {
                limit = weglot_languages.plans.starter_free.limit_language;
            } else if( weglot_languages.plans.business.ids.indexOf(plan) >= 0 ) {
                limit = weglot_languages.plans.business.limit_language;
            }

            destination_selectize[0].selectize.settings.maxItems = limit
        });

    }

    document.addEventListener('DOMContentLoaded', () => {
        execute();
    })
}

export default init_admin_select;
