<div class="boxRound">
<div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
  <h4><?php echo Utilities::getLabel('L_Browse_by_categories')?></h4>
</div>
<div class="box_Middle toggleWrap">
    <div class="listscroll">
      <ul class="vertical_links">
        <li>
          <ul>
            {{#categories}}
    			<li>
	    			<li><a href="{category_url}">{{category_name}}</a></li>
    		    </li>
   			 {{/categories}}
          </ul>
        </li>
      </ul>
     </div> 
</div>
</div>