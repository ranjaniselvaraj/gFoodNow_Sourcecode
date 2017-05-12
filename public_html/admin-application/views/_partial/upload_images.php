<div id="elem_main_image_div">
    <div class="fieldadd" id="image_div" >
        <div class="grid_1">
            <div class="filefield">
                <span class="filename file_input_name" >Recommended image size: 800x400px, Supported Formats: jpg, jpeg, png or gif.</span>
                <input type="file" id="uploadBtn1" name="post_image_file_name[]" onChange="setFileInputName(this)"><label class="filelabel">Browse File </label>
            </div>
			
        </div>
        <div class="grid_2">
            <ul class="actions">
                <li><a href="javascript:void(0)" onClick="addMoreImages();" title="Add More" ><span class="ink animate" style="height: 38px; width: 38px; top: 0.450012px; left: -2.43335px;"></span><i class="ion-plus-round icon"></i></a></li>
            </ul>
        </div>
	 
    </div>
</div>
<div id="clone_image_div" style="display:none;">
    <div class="fieldadd"  >
        <div class="grid_1">
            <div class="filefield"><span class="filename file_input_name">Recommended image size: 800x400px, Supported Formats: jpg, jpeg, png or gif.</span>
                <input type="file" id="uploadBtn1" name="post_image_file_name[]" onChange="setFileInputName(this)"><label class="filelabel">Browse File</label>
            </div>
			
        </div>
        <div class="grid_2">
            <ul class="actions">
                <li><a href="javascript:void(0)" onClick="removeImageInput(this);" title="Add More"><i class="ion-minus icon"></i></a></li>
            </ul>
        </div>
	 
    </div>
</div>
 
