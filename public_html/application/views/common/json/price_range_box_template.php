<div class="boxRound">
                <div class="boxTop"><a href="javascript:void(0)" class="openToggle toggleLink"></a>
                  <h4><?php echo Utilities::getLabel('L_Price')?></h4>
                </div>
                <div class="box_Middle toggleWrap">
                  <div class="listscroll">
                    <ul class="labelList ajax-filters">
                      {{#price_ranges}}
                      <li>
                        <label><span class="span1">
                          <input class="price_range" {{#is_price_range_checked}} checked="checked" {{/is_price_range_checked}}  name="price_range[]" type="checkbox" value="{{min}}-{{max}}">
                          </span><span class="span2">{{min_formatted}} <?php echo Utilities::getLabel('L_and')?> {{max_formatted}} (<strong>{{items}}</strong>)</span></label>
                      </li>
                      {{/price_ranges}}
                    </ul>
                  </div>
                  <div class="clearlink"> <a href="javascript:void(0)" class="clear_all"><?php echo Utilities::getLabel('L_Clear_All')?></a> </div>
                </div>
              </div>
              