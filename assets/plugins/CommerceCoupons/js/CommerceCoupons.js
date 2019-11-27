var CommerceCoupons = {
    bind: function() {
        var self = this;
        $(document).on("click", "[data-commerce-coupon-add]", function(e){
            e.preventDefault();
            self.add();
        })
    },
    add: function(){
        if ($("[data-commerce-coupon]").length == 1) {
            var coupon = $("[data-commerce-coupon]").val();
            if (coupon == '') {
                $(document).trigger("commerce-coupon-empty");
            } else {
                var responce = this.sendRequest("&action=commercecoupons&coupon=" + coupon);
            }
        }
    },
    sendRequest: function(data) {
        var self = this;
        $.ajax({
            url: "assets/plugins/CommerceCoupons/ajax.php",
            data: data,
            type: "POST",
            cache: false,
            dataType: 'json',
            beforeSend:function(){
                //form_loader.show();
            },                   
            success: function(msg){
                var responce = "commerce-coupon-" + msg.status + '-' + msg.message;
                $(document).trigger(responce);
                console.log(responce);
            }
        })
    }
}
/* events
// commerce-coupon-empty
// commerce-coupon-error-unactive
// commerce-coupon-error-limits
// commerce-coupon-ok-add
*/
$(document).ready(function(){

    CommerceCoupons.bind();

    $(document).on("commerce-coupon-ok-add", function(){
        Commerce.updateCarts();
    })
    $(document).on("commerce-coupon-error-limits", function(){
        Commerce.updateCarts();
    })
    $(document).on("commerce-coupon-error-unactive", function(){
        Commerce.updateCarts();
    })
})
