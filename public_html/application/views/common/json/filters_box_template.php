{{#filtergroups}}
	{{#display_filter_group}}
    <div class="boxRound">
    <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
      <h4>{{name}}</h4>
    </div>
    <div class="box_Middle toggleWrap">
      <div class="listscroll">
        <ul class="labelList">
          {{#filters}}
            <li {{#is_disabled}} class="disabled" {{/is_disabled}}>
            <label><span class="span1"><input name="filters[]" 
            {{#is_filter_checked}} checked="checked" {{/is_filter_checked}} type="checkbox" class="filter_range" value="{{filter_id}}" ></span><span class="span2">{{name}}</span></label>
            </li>
         {{/filters}}
        </ul>
      </div>
      <div class="clearlink"> <a href="javascript:void(0)" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
    </div>
    </div>
	{{/display_filter_group}}
{{/filtergroups}}