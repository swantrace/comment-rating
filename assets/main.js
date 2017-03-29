(function($){
	$("#comment-rating-field").rateYo({
		rating: 3,
        numStars: 5,
        precision: 1,
        minValue: 1,
        maxValue: 5,
        halfStar: true
    }).on("rateyo.change", function(e, data){
    	var rating = data.rating;
    	$("#comment-rating-input-field").val(rating);
    })

})(jQuery); 