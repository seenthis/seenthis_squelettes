if (langue_visiteur) var language = langue_visiteur;
else if (navigator.browserLanguage) var language = navigator.browserLanguage;
else var language = navigator.language;

if (language.indexOf('fr_tu') > -1) language = "fr_tu";
else if (language.indexOf('fr') > -1) language = "fr";
else if (language.indexOf('nl') > -1) language = "nl";
else if (language.indexOf('en') > -1) language = "en";
else if (language.indexOf('de') > -1) language = "de";
else if (language.indexOf('es') > -1) language = "es";
else if (language.indexOf('ar') > -1) language = "ar";
else language = "fr";

var traduire_avec_google = "traduire avec Google";

$('body').on('mouseenter mouseleave', '#messages li', function() {
	var me=$(this),r,id_me;
	if (!(r = (me.attr('id') ||'').match(/message(\d+)/))
	|| me.hasClass('cooked'))
		return;
	id_me = r[1];
	me.addClass('cooking').addClass('cooked');

	//console.log(me);
	me.removeClass('cooking');
});



function switch_comments(id_me) {
	$('.yourmessage').show(); 

	$('.repondre').stop().slideUp("fast");

	if (!($('#repondre'+id_me).is(':visible') && ($('#repondre'+id_me).height() > 10)) ) {
		$('.formulaire_poster_message').removeClass('focus');
		$('#yourmessage'+id_me).hide();
		$('#repondre'+id_me).stop().slideDown("fast").find('.formulaire_poster_message').addClass('focus').find('textarea').show().focus();
	}
}

$.fn.soundmanager = function() {
	return $(this)
	.each(function(){
		var url_son = $(this).find('source[type="audio/mpeg"]').attr('src');
		$(this).html('<div class="lecture"><button class="play">play</button></div><div class="controles"><div class="track sans_titre"><a title="Ecouter" rel="enclosure" href="' + url_son + '"><span class="fn"> </span></a></div><div class="progress_bar"><div class="position"></div><div class="loading"></div></div><div class="lesinfos"><div class="time"></div><div class="duration"></div></div></div><br style="clear:both;">');
		sound_manager_init();
	});
}


	function favoris_actifs() {
	
		if (auteur_connecte > 0) {
			$(".texte_message, .texte_reponse").each(function() {
				
				var rel = $(this).find(".favori").children("a.activer_favori").attr("rel");
				var reg = 	new RegExp(rel, "gi");
				if (auteur_connecte.match(reg)) {
					$(this).find(".favori a.activer_favori").addClass("actif");
				} else {
					if ($(this).find(".favori .survol").length > 0) {
						$(this).find(".favori a.activer_favori").addClass("abonnes");
					} else {
						$(this).find(".favori a.activer_favori").addClass("inactif");
					}
				}
			});	
		} else {
			$(".favori").css("display", "none");
		}
	}
	
	$.fn.afficher_masques = function() {
		$(this).parents('ul.reponses').children('.masquer').each(function(){
			$(this).fadeIn();
		}); 
		$(this).slideUp(function(){
			$(this).remove();
		});
	}
	
	$.fn.suivreEdition = function () {
		area = this;
			var texte_message = area.val();
			
			var people = "<div class='titre_people'>Auteurs:</div>";
			var p = texte_message.match(reg_people);
			
			if (p) {
				for(i=0; i<p.length; ++i) {
					personne = p[i];
					nom = personne.substr(1,1000);
					lien = "people/"+nom;
					people = people +  "<span class='nom'><span class='diese'>@</span><a href=\""+lien+"\" class='spip_out'>"+nom+"</a></span>";
				}
				area.parent("div").parent("form").children(".people").html(people);
				area.parent("div").parent("form").children(".people").slideDown();
		
			} else {
				area.parent("div").parent("form").find(".people").slideUp();			
			}
			
			
			var tags = "<div class='titre_tags'>Thèmes:</div>";
			var m = texte_message.match(reghash);
			
			
			if (m) {
				for(i=0; i<m.length; ++i) {
					tag = m[i].toLowerCase();
					lien = tag.substr(1,1000);
					var affclass = "";
					
					tags = tags +  "<span class='hashtag" + affclass + "'><span class='diese'>#</span><a href=\"tag/"+lien+"\" class='spip_out'>"+lien+"</a></span>";
				}
				area.parent("div").parent("form").find(".tags").html(tags);
				area.parent("div").parent("form").find(".tags").slideDown();
			} else {
				area.parent("div").parent("form").find(".tags").slideUp();
			}
			
			
			
			var liens = "<div class='titre_links'>Liens:</div>";
			var u = texte_message.match(url_match);
			if (u) {
				for(i=0; i < u.length; ++i) {
					var lien = u[i];
					var lien_aff = lien.replace(racine_url_match, "<span>$1</span>");
					var lien_aff = lien_aff.replace(fin_url_match, "<span>$1</span>");
					
					liens = liens +  "<div class='lien'>⇧<a href=\""+lien+"\" class='spip_out'>"+lien_aff+"</a></div>";
				}
				area.parent("div").parent("form").find(".liens").html(liens);
				area.parent("div").parent("form").find(".liens").slideDown();
			} else {
				area.parent("div").parent("form").find(".liens").slideUp();
			}
	
	}

	function afficher_traduire() {
		$("blockquote").each(function() {
			var me = $(this);
			me.find(".traduire").remove();
			var langue = me.attr("lang");
			if (langue!="" && langue != language) {
				var contenu = encodeURIComponent(me.html());
				me.append("<div class='traduire'><a href='#'>"+traduire_avec_google+"</a></div>");
				me.find(".traduire").bind("click", function() {
					me.attr("lang", "").find(".traduire").remove();
						me.html("<div class='loading_icone'></div>");
						$.post("index.php?page=translate", { contenu: contenu+" ", dest: language, source: langue }, function (data) {
						me.html(data);
					});
					return false;
				});
			}
		});
	}



	function sucrer_utm(u) {
		u = u.replace(/(http:\/\/twitter.com\/)#!/, "$1");
		u = u.replace(/([\?\&]|\&amp;)utm\_.*/, "");
		u = u.replace(/#xtor.*$/, "");
		return u;
	}

	$(function(){

		var vals = {};
		$.each([ 'content', 'ajouter', 'url_site', 'extrait' ], function () {
			var r;
			var re = new RegExp ('[#?&]'+ this +'=([^&]*)');
			if (r = window.location.href.match(re)) {
				vals[this] = $.trim(decodeURIComponent(r[1]));
			}
		});
		var content = "";
		if (vals['content']) {
			content += vals['content'];
		}
		if (vals['ajouter']) {
			content += vals['ajouter'].replace(/@/, ' ')+"\n";
		}
		if (vals['url_site']) {
			content += sucrer_utm(vals['url_site'])+"\n";
		}
		if (vals['extrait']) {
			content += "\n❝" + vals['extrait'] + "❞\n";
		}

		// si on a un content mais qu'on n'est pas loge, l'enregistrer
		// temporairement dans un cookie, et le restituer apres connexion
		if ($('#formulaire_login').size()) {
			if (content)
				$.cookie('content', content);
		} else {
			if ($.cookie('content')) {
				content = $.cookie('content');
				window.location.hash="content="+content;
				$.cookie('content', null);
			}
			$(".formulaire_principal textarea").val(content);
		}



		$.ajaxSetup({ cache: true });
		if (langue_visiteur && langue_visiteur != "fr") {
				$.getScript("index.php?page=js.calcul_date&lang="+langue_visiteur)
		}
		if (langue_visiteur) {
			$.getScript("index.php?page=js.textes_interface&lang="+langue_visiteur)
		}
		else {
			var lang_id = "";
			if ($.cookie('lang_id')) lang_id = $.cookie('lang_id');
			else {
				lang_id = Math.floor(Math.random() * 1000000);
				$.cookie('lang_id', lang_id, { expires: 1 });
			}
		
			$.getScript("index.php?page=js.textes_interface&lang_id="+lang_id)
			
		}


		function charger_alertes() {
			$.get('index.php?page=alertes', function(e) {
				$.each($('li', e), function(i,j) {
					var id = $(j).attr('id');
					if (id && !$("#"+id).is('li'))
						$('#alertes ul')
						.prepend($(j).addClass('nouvelle_alerte'));
				});
			});
		}

		var time_alert = $.timeout ( function () {
				$("#alertes").load("index.php?page=alertes", function() {
					$("#alertes").slideDown();
					// charger les nouvelles alertes de temps en temps (3min)
//					setInterval(charger_alertes, 180000);
				});
			}, 500);

		if (auteur_connecte > 0) {
			$('body').on("click", ".bouton_repondre a", function() {
				id = $(this).attr("rel");
				switch_comments(id);
				return false;
			});
			if (window.auteur_page !== undefined) {
				if (auteur_connecte != auteur_page) {
					$("body.people .messager").show();
				}
			}
		} else {
			$(".bouton_repondre a").hide();
			$("body.message .page_auteur").show();
		}
		
		

		if ($("body").hasClass("people") &&  auteur_connecte > 0 && auteur_connecte != auteur_page) {
			$("#follow").load('index.php?action=bouton_follow_people&id_auteur='+auteur_page);
		}

		if ($("body").hasClass("mot") &&  auteur_connecte > 0) {
			var tag = $("#follow_mot").attr('data-tag');
			var type = $("#follow_mot").attr('data-type');
			$("#follow_mot").load('index.php?action=bouton_follow_mot&tag='+encodeURIComponent(tag));
		}

		favoris_actifs();
		afficher_traduire();
		if (language == "ar"){
			$("body").attr("dir", "rtl").addClass("lang-ar");
			
			if ( $("#css_rtl").length>0 ) {
				$("#css_rtl").attr("rel", "stylesheet");
				$("#css_default").attr("rel", "alternate stylesheet");
			}
		}
		
		$("ul#scroll_tags").liScroll();
		
		$('body').on("click", "a.spip_out", function() {
			window.open($(this).attr("href"));
			return false;
		});
	
		//$('textarea').autoResize();


		$("#recherche").focus(function() {
			$("#entete").addClass("rechercher");
		});

		$("#recherche").focusout(function() {
			$("#entete").removeClass("rechercher");
		});

		$('body')

		.on("keydown", 'textarea', function(e) {
			var area = $(this);
			var keyCode = e.keyCode || 0;

			// (shift ou ctrl) + enter (valider)
			if (keyCode == 13 && (isShift || isCtrl)) {
				isShift = false;
				area.submit();
				return false;
			}
			// shift + tab (citer)
			if (keyCode == 9 && isShift) {
				isShift = false;
				$(this).replaceSelection("\n❝" + $(this).getSelection().text + "❞\n", true);
				return false;
			}

			// detecter le shift
			isShift = (keyCode == 16);
			// detecter le ctrl
			isCtrl  = (keyCode == 17);
		})

		.on("keyup", 'textarea', function(e) {
			isShift = false;
			var area = $(this);
			$.idle(function() { area.suivreEdition(); }, 1000);
		})

		.on("click", '.formulaire_poster_message  textarea', function() {
			$(".formulaire_poster_message").removeClass('focus');
			$(this).parents(".formulaire_poster_message").addClass('focus');
		})

		.on("click", 'input[type=reset]', function() {
			$('.formulaire_poster_message').removeClass('focus');
			$(this).parents(".formulaire_poster_message").find("textarea").val("").suivreEdition();
			$('.yourmessage').show(); 
			$('.repondre').hide();
			return false;
		})

		.on('focus', 'textarea', function() {
			var ta = $(this);
			setTimeout(function() {ta.elastic();}, 100);
		})

		.on("mouseenter", ".texte_message, .texte_reponse", function() {
			
			if (auteur_connecte > 0) {
				$(this).find(".favori a.inactif").show();
			}
			
			var rel = $(this).find(".modifier").children("a").attr("rel");
			if (auteur_connecte == rel) {
				$(this).find(".modifier").children("a").show();
				$(this).find(".modifier_themes").show();
			}
			
			var rel = $(this).find(".supprimer").children("a").attr("rel");
			var reg = 	new RegExp(rel, "gi");
			if (auteur_connecte.match(reg)) {
				$(this).find(".supprimer").children("a").show();
			}

		})
		.on("mouseleave", ".texte_message, .texte_reponse", function() {
			$(this).find(".supprimer").children("a").hide();
			$(this).find(".modifier").children("a").hide();
			$(this).find(".favori a.inactif").hide();
		});
		//afficher_oe();

		// soundmanager
		var soundmanager = function() {
			$('div.audio:not(.soundmanager)')
			.addClass('soundmanager')
			.soundmanager();
		};
		setTimeout(soundmanager, 100);
		setInterval(soundmanager, 2000);

	});

	/* Activer quand on charge un element de page en Ajax 
	   pour les trucs qui ne fonctionnent pas en mode live */
	$(document).ajaxComplete(function(){
		//$('textarea').autoResize();
		favoris_actifs();
	});


