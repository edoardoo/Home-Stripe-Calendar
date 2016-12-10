function updateSliderPosition(){
    slider.css({'left': '-'+100*sliderIndex+'%'});
}

function incrementIndex(){
    if( sliderIndex <= sectionsNumber){
        sliderIndex++;
    }else{
        sliderIndex = 0;
    }

}

function subtractIndex(){
    if( sliderIndex > 0){
        sliderIndex--;
    }
}

function setArrows(){
    if( sliderIndex == 0 ){
        jQuery(".calendarArrow.back").addClass('disabled');

    }else{
        jQuery(".calendarArrow.back").removeClass('disabled');
    }

    if( sliderIndex < sectionsNumber-1){
        jQuery(".calendarArrow.next").removeClass('disabled');

    }else{
        jQuery(".calendarArrow.next").addClass('disabled');

    }
}

function initPlugin(){
    sliderIndex = 0;
    slider = jQuery("#calendarPlugin");
    sliderElements = slider.children().length;
    var screenDimension = document.body.clientWidth;
    elementsPerRow = (screenDimension > 1000) ? 4 : (screenDimension > 500) ? 2 : 1;
    sectionsNumber = Math.ceil(sliderElements/elementsPerRow);
}

function setListeners(){
    jQuery(".calendarArrow").on( 'click', function(){

        if(!jQuery(this).hasClass('disabled')){

            if( jQuery(this).hasClass('next') ){
                incrementIndex();
            }else{
                subtractIndex();
            }

            updateSliderPosition();
            setArrows();
        }


jQuery('.eventDetail a').click(function(event) {
  event.preventDefault();
  
});



    });
}

function loadEventDetails( id, date){
    var url = 'http://sviluppoweb.senzafiltro.it/isfitaly/wp-admin/admin-ajax.php?action=spidercalendarbig&theme_id=13&calendar_id=1&ev_ids='+id+'&eventID='+id+'&date='+date+'&many_sp_calendar=1&cur_page_url=http://sviluppoweb.senzafiltro.it/isfitaly/';
    jQuery.get(url, function(data){
        jQuery(".closeDetails").removeClass("hidden");
        jQuery("#showEventDetails").html(data);
    });
}

function closeEventDetails(){
    jQuery("#showEventDetails").html("");
    jQuery(".closeDetails").addClass("hidden");


}

jQuery(document).on('ready', function(){
    initPlugin();
    setListeners();
    setArrows();


    jQuery(window).on('resize', function(){

        initPlugin();
        setArrows();
		//console.log("data: epr "+ elementsPerRow +",  sectionsNumber:"+sectionsNumber)
    });

});
