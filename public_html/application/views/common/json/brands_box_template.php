<div class="boxRound ">
    <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
    	<h4><?php echo Utilities::getLabel('L_Brand')?> </h4>
    </div>
    <div class="box_Middle toggleWrap">
    	<div class="listscroll">
    	<ul class="labelList ajax-filters" id="ulBrands">
    	{{#brands}}
    	<li {{#is_disabled}} class="disabled" {{/is_disabled}}>
	    <label><span class="span1"><input name="brand[]" 
    	{{#is_brand_checked}} checked="checked" {{/is_brand_checked}} type="checkbox" class="brands" value="{{brand_id}}" ></span><span class="span2">{{brand_name}}</span></label>
    	</li>
   		 {{/brands}}
    	</ul>
    	</div>
    <div class="clearlink"> <a href="#" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
    </div>
</div>