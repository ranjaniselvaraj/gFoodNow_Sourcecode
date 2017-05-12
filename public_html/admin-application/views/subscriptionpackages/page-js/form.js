var countSubs= 1;
$(document).delegate('.addMoreSubs' , 'click' , function(){
	if(countSubs<=4){
		var elm = $('td').find('div.merged:first').clone();//.html();
		var appended = $(elm).appendTo($('div.merged').parent());
		$(appended).addClass('addedDiv');
		$(appended).append('<a class="right removeAddedSub" href="javascript:void(0)" >Remove</a>');
		$(appended).addClass('addedDiv');
		$(appended).find('ul.errorlist').remove(); /* to not clone error list with the structure */
		
		$(appended).find(':input').each(function(){
			debugger;
			$(this).removeClass('error'); /* to not clone error list with the structure */
			$(this).val('');
			var name = $(this).attr('name');
			var newName = $(this).attr('name') + countSubs;
			PackagesfrmValidator_requirements[newName] = PackagesfrmValidator_requirements[name];
			$(this).attr('name' ,newName );
			
		});
		PackagesfrmValidator.resetFields();
		countSubs++;
		RecountItems();
	}
	else{
		alert('Maximum 5 Entries are allowed.');
	}
});
var removedSubs = new Array();
$(document).delegate('.removeAddedSub' , 'click' , function(){
	if(confirm('Are you sure , You wan\'t to remove the entry ?'))
	{
		var divToRemove = $(this).closest('div.addedDiv');
		
		$(divToRemove).find(':input').each(function(){
			
			var name = $(this).attr('name');
			delete PackagesfrmValidator_requirements[name];
		});
		
		PackagesfrmValidator.resetFields();
		removedSubs.push(divToRemove.find(':input.merchantsubpack_id').val());
		$(this).closest('div.addedDiv').remove();
		countSubs--;
		RecountItems();
	}
});

function RecountItems(){
		if(countSubs>=5){
			$('.addMoreSubs').hide();
		}else{
			$('.addMoreSubs').show();
		}
}

$(document).ready(function(){
	
	/* append controls to Subscriptions billing */
	var $elms = $('.merge').closest('tr').detach();
	$('div.merged').append($elms);
	$(frmPackages).bind('submit' , function(){
		var jsonData = new Array();
		$('div.merged').each(function(indx , elem){
			var elm = $(elem); 
			jsonData[indx] = {
				merchantsubpack_id : elm.find('input.merchantsubpack_id').val() ,
				merchantsubpack_actual_price : elm.find('input.merchantsubpack_actual_price').val() ,
				merchantsubpack_recurring_price : elm.find('input.merchantsubpack_recurring_price').val() ,
				merchantsubpack_subs_frequency : elm.find('input.merchantsubpack_subs_frequency').val() ,
				merchantsubpack_subs_period : elm.find('select.merchantsubpack_subs_period').val(), 
				//merchantsubpack_subs_period : elm.find('select.merchantsubpack_period').val() ,
				merchantsubpack_total_occurrance : elm.find('input.merchantsubpack_total_occurrance').val() ,
				merchantsubpack_active : elm.find('select.merchantsubpack_active').val() 
			} ;
		});
		
		$('#data').val(JSON.stringify(jsonData));
		$('#data_removed').val(JSON.stringify(removedSubs));
	});
	
	var data = $('#data').val();
	var dataObj = $.parseJSON(data);
	var count = 0;
	for(var idx in dataObj)
	{
		if(count == 1)
		{
			$('.addMoreSubs').click();
		}
		var elm = $('div.merged')[idx] ;
		$(elm).find('input.merchantsubpack_id').val(dataObj[idx]['merchantsubpack_id']);
		$(elm).find('input.merchantsubpack_actual_price').val(dataObj[idx]['merchantsubpack_actual_price']);
		$(elm).find('input.merchantsubpack_recurring_price').val(dataObj[idx]['merchantsubpack_recurring_price']);
		$(elm).find('input.merchantsubpack_subs_frequency').val(dataObj[idx]['merchantsubpack_subs_frequency']);
		$(elm).find('select.merchantsubpack_subs_period').val(dataObj[idx]['merchantsubpack_subs_period']);
		$(elm).find('input.merchantsubpack_total_occurrance').val(dataObj[idx]['merchantsubpack_total_occurrance']);
		$(elm).find('select.merchantsubpack_active').val(dataObj[idx]['merchantsubpack_active']);
		count = 1;
	}
	
});