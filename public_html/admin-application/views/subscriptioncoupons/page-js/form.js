function getSubPackages($packageId){
	callAjax(generateUrl('common', 'loadDropDown', [$packageId , 'SUBPACKAGES'], webroot), 'a=1', function(data){ 
		
			data = parseJsonData(data);
			if(typeof data == "object")
			{
				$('#ddlSubPackage').html('');
				var opt = "<option value=''>Select</option>";
				$('#ddlSubPackage').append(opt);
				for(var i in data)
				{
					var packageName = data[i];
					opt = "<option value='"+i+"'>"+packageName+"</option>";
					$('#ddlSubPackage').append(opt);
				}
			}
		});
}