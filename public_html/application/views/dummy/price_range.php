<?php defined('SYSTEM_INIT') or die('Invalid Usage'); ?>
<div>
<div class="boxRound box_price_range">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Price')?> <span>(<?php echo CONF_CURRENCY_SYMBOL?>)</span></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="filter-content">
                    <div class="space-lft-right-low marginTop"><div id="slider" class="price_range_slider"></div></div>
                    <div class="prices"></div>
                    <!--<div class="prices"> <span class="from-price-text">Rs 215</span> <span class="to-price-text">Rs 61203</span> </div>-->
                    <div class="clear"></div>
                    <div class="price-input">
                      <div class="price-text-box">
                        <input class="input-filter form-control" readonly="readonly" data-index="0" min="<?php echo $min_start?>" max="<?php echo $max_start?>" value="<?php echo $min_start?>">
                        </div>
                    </div>
                    <span class="dash"> - </span>
                    <div class="price-input">
                      <div class="price-text-box">
                        <input class="input-filter form-control" readonly="readonly" data-index="1" min="<?php echo $min_start?>" max="<?php echo $max_start?>" value="<?php echo $max_start?>">
                        </div>
                    </div>
                    </div>
                  <div class="clearlink"> <a href="#" class="clear_price"><?php echo Utilities::getLabel('L_Clear')?></a> </div>
                </div>
              </div>
</div>
<script type="text/javascript">	
//alert(parseFloat(92));
$(document).ready(function() {
    $("#slider").slider({
        min: 10,
        max: 5000,
        step: 1,
        values: [400, 3300],
        slide: function(event, ui) {
            for (var i = 0; i < ui.values.length; ++i) {
                $("input.input-filter[data-index=" + i + "]").val(ui.values[i]);
				$("input.price_range[data-index=" + i + "]").val(ui.values[i]);
            }
        },
		stop: function( event, ui ) {resetFormPagingandSearch();}
    });
    $("input.input-filter").change(function() {
        var $this = $(this);
		$("input.price_range[data-index=" + $this.data("index") + "]").val($this.val());
		resetFormPagingandSearch();
    });
});
$('body').on('click','.clear_price',function(event){
        event.preventDefault();
		$("input.price_range[data-index=0]").val('');
		$("input.price_range[data-index=1]").val('');
		resetFormPagingandSearch();
});	
</script>