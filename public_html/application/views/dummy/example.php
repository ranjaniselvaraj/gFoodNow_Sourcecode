<script type="text/javascript">
    $(function(){
		setInterval(function(){  var href=generateUrl('cart', 'checkout_sidebar');
		callAjax(href,'', function(response){ var json = parseJsonData(response); alert(json.html); }) }, 5000);
	})
</script>	