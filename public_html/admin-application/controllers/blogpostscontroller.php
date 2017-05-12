<?php
class BlogpostsController extends BackendController {
    private $admin;
    private $admin_id = 0;
    private $post_status;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->post_status = Applicationconstants::$post_status;
		$this->canview = Admin::getAdminAccess($admin_id,BLOG_POSTS);
		$this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();		
        $this->b_crumb->add("Blog Post Management", Utilities::generateUrl("blogposts"));
    }
    function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmPost', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    function listBlogPosts($page = 1) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btn_submit'])) {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0) {
                $page = intval($post['page']);
            } else {
                $post['page'] = $page;
            }
            if (!empty($post['post_title']) || (isset($post['post_status'])) && $post['post_status'] != '') {
                $this->set('srch', $post);
            }
           $pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $this->Blogposts->getBlogpostsData($post));
            $this->set('pages', $this->Blogposts->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blogposts->getTotalRecords();
			
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->_template->render(false, false);
        }
    }
    private function getSearchForm() {
        $frm = new Form('frmPostSearch', 'frmPostSearch');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
		$frm->setTableProperties('class="table_form_vertical"');
        $frm->setFieldsPerRow(4);
        $frm->setLeftColumnProperties('width="20%"');
        $frm->captionInSameCell(true);
        $frm->addTextBox('Post Title', 'post_title', '', 'post_title', '');
        $frm->addSelectBox('Post Status', 'post_status', $this->post_status, '', '', 'Select', 'post_status');
        $fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick="clearSearch()"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
        $frm->addHiddenField('', 'page', 1);
		$frm->setOnSubmit('searchPost(this); return false;');
        return $frm;
    }
    function add() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getPostForm();
		
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post['post_seo_name'] = $post['post_seo_name'];  
           
            if (!$frm->validate($post)) {
                Message::addErrorMessage($frm->getValidationErrors());
                $this->set('relation_category_id', $post['relation_category_id']);
                $frm->fill($post);
            } else {
				$error = 0;
                if (!empty($_FILES['post_image_file_name']['name'])) {
                    $image_names = array();
                    if (!$this->saveUploadedPostFiles($_FILES, $image_names)) {
						$frm->fill($post);
						$error = 1;
                    }
					$post['post_image_file_name'] = $image_names;
                }  
				if( $error == 0 ){
					$post['post_comment_status'] = CONF_POST_COMMENT_STATUS;
					if (isset($post['btn_submit'])) {
						if ($this->Blogposts->addUpdate($post)) {
							Message::addMessage('Post added successfully.');
							Utilities::redirectUser(Utilities::generateUrl('blogposts'));
						} else {
							Message::addErrorMessage($this->Blogposts->getError());
						}
					} else if (isset($post['btn_publish'])) {
						$post['post_status'] = 1;
						if ($this->Blogposts->addUpdate($post)) {
							Message::addMessage('Post added successfully.');
							Utilities::redirectUser(Utilities::generateUrl('blogposts'));
						} else {
							Message::addErrorMessage($this->Blogposts->getError());
						}
						
					}
				}
            }
        }
         
        $categories = $this->Blogposts->getAllCategories();
        $categories1 = $this->sortCategories($categories);
	  
        $this->set('categories', $categories1);
		$this->set('categoriesStructure', $this->loadCategoryStructure($categories1,0,array()));
        $this->b_crumb->add("Add Post", Utilities::generateUrl("blogposts", "add"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->set('frmAdd', $frm);
        $this->_template->render();
    }
	 
    function edit($blog_post_id) {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $blog_post_id = intval($blog_post_id);
        if ($blog_post_id < 1) {
            dieWithError("Unauthorized Access");
        }
        $post_images = $this->Blogposts->getPostImages($blog_post_id);
        $frm = $this->getPostForm();
        $frm->getField('category_title')->extra = '';
        $post = Syspage::getPostedVar();
     
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post['post_seo_name'] = $post['post_seo_name'];  
          
            if (!$frm->validate($post)) {
                $this->set('relation_category_id', $post['relation_category_id']);
                $frm->fill($post);
                Message::addErrorMessage($frm->getValidationErrors());
            } else {
                if (!empty($_FILES['post_image_file_name']['name'])) {
                    $image_names = array();
                    if (!$this->saveUploadedPostFiles($_FILES, $image_names)) {
						Utilities::redirectUser(Utilities::generateUrl('blogposts', 'edit', array($blog_post_id)));
                    }
					$post['post_image_file_name'] = $image_names;
                }
                if (!isset($post['btn_publish'])) {
                    if ($post['post_id'] != $blog_post_id) {
                        dieWithError("Unauthorized Access");
                    } else {
                        $remove = array();
                        if (isset($post['post_removed_images']) && strlen($post['post_removed_images']) > 0 && $this->setRemoveImageData($post['post_removed_images'], $post_images['imgs'], $remove)) {
                            $post['remove_post_images'] = $remove;
                        }
                        if ($this->Blogposts->addUpdate($post)) {
                            Message::addMessage('Post updated successfully.');
                            Utilities::redirectUser(Utilities::generateUrl('Blogposts', ''));
                        } else {
                            Message::addErrorMessage($this->Blogposts->getError());
                        }
                    }
                } elseif (isset($post['btn_publish'])) {
                    $post['post_status'] = 1;
                    $remove = array();
                    if (isset($post['post_removed_images']) && strlen($post['post_removed_images']) > 0 && $this->setRemoveImageData($post['post_removed_images'], $post_images['imgs'], $remove)) {
                        $post['remove_post_images'] = $remove;
                    }
                    if ($this->Blogposts->addUpdate($post)) {
                        Message::addMessage('Post published successfully.');
                        Utilities::redirectUser(Utilities::generateUrl('Blogposts', ''));
                    } else {
                        Message::addErrorMessage($this->Blogposts->getError());
                    }
                }
            }
        }
        $post_data = $this->Blogposts->getBlogPost($blog_post_id);
        $relation_category_ids = $post_data['relation_category_ids'];
        $relation_category_ids = explode(',', $relation_category_ids);
        $this->set('relation_category_id', $relation_category_ids);
		
        $frm->fill($post_data);
 
        if (isset($post_images['imgs']) && is_array($post_images['imgs']) && sizeof($post_images['imgs']) > 0) {
             
            $photo_html .= '<div class="photosrow" id="post_imgs">';
            $photo_html .= $this->getImagesHtml($post_images, $blog_post_id, 'post');
            $photo_html .= '</div>';
           
            $this->set('photo_html', $photo_html);
        }
        $frm->getField('post_title')->extra = '';
        $categories = $this->Blogposts->getAllCategories();		
        $categories1 = $this->sortCategories($categories);		
        $this->set('post_data', $post_data);
        $this->set('categories', $categories1); 
		$this->set('categoriesStructure', $this->loadCategoryStructure($categories1,0,$relation_category_ids));
        $this->set('frmEdit', $frm);
        Syspage::addJs(array(CONF_THEME_PATH . 'blogposts/page-js/add.js'));
        $this->b_crumb->add("Edit Post", Utilities::generateUrl("blogposts", "edit"));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render(true,true,'',false,false);
    }
	
    private function getPostForm() {
        $frm = new Form('frmPost', 'frmPost');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('class="table_form_horizontal" ');
        $frm->setLeftColumnProperties('width="25%"');
        $frm->addRequiredField('Post Title', 'post_title', '', 'post_title', 'onblur="setSeoName(this, post_seo_name)"');
        $fld_cat = $frm->addRequiredField('Post SEO Name', 'post_seo_name', '', 'post_seo_name', 'onblur="setSeoName(this, post_seo_name)"');
        $fld_cat->setUnique('tbl_blog_post', 'post_seo_name', 'post_id', 'post_id', 'post_id');
        $frm->addTextBox('Post Contributor Name', 'post_contributor_name', '', 'post_contributor_name');
        $frm->addTextArea('Post Short Description', 'post_short_description', '', 'post_short_description');
        $frm->addHtmlEditor('Post Content', 'post_content', '','post_content')->requirements()->setRequired(true);
        $frm->addSelectBox('Post Status', 'post_status', $this->post_status, '0', '', '', 'post_status');
        $frm->addSelectBox('Post Comment Status', 'post_comment_status', array(0 => 'Not Open', 1 => 'Open'), CONF_POST_COMMENT_STATUS, '', '', 'post_comment_status');
        $frm->addFileUpload('Post Image', 'post_image_file_name[]', '', 'accept="image/*"');
        $frm->addTextBox('Meta Title', 'meta_title', '', 'meta_title');
        $frm->addTextArea('Meta Keywords', 'meta_keywords', '', 'meta_keywords');
        $frm->addTextArea('Meta Description', 'meta_description', '', 'meta_description');
		$fld = $frm->addHtml('', '', 'Note: Meta Others are HTML meta tags, e.g &lt;meta name="example" content="example" /&gt;. We are not validating these tags, please take care of this.');
        $frm->addTextArea('Meta Others', 'meta_others', '', 'meta_others')->attachField($fld);
        $frm->addHiddenField('', 'post_id', '', 'post_id');
        $frm->addHiddenField('', 'meta_id', '', 'meta_id');
        $frm->addHiddenField('', 'post_removed_images', '', 'post_removed_images');
        $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit');
		$frm->setValidatorJsObjectName('frmPost_validator');
		$frm->setOnSubmit('return validatePost(this, frmPost_validator);');
        $frm->addSubmitButton('', 'btn_publish', 'Publish', 'btn_publish')->html_after_field = '<input type="button"  class="" value="Cancel" onclick = "cancelPost();">';
        return $frm;
    }
	
	private function sortCategories(array $elements, $parentId = 0) {		
        $branch = array();
        foreach ($elements as $catid=>$element) {
            if ($element['category_parent'] == $parentId) {
                $children = $this->sortCategories($elements, $element['category_id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $branch[$catid] = $element;
            }
        }		
        return $branch;
    }
	
	function loadCategoryStructure($categories,$level=0,$relation_category_id){
		$str='';													
		foreach ($categories as $categories1) {
			$checked = '';
			if(isset($relation_category_id)) {
				foreach ($relation_category_id as $relation_category_id1) {
					if ($relation_category_id1 == $categories1['category_id']) {
						$checked = 'checked="checked"';
					}
				}
			}
			$str.='<li>
					<label class="checkbox leftlabel">
						<input type="checkbox" title="parent category" onchange="validateCheckbox();" name="relation_category_id[]" id="relation_category_id" value=' . $categories1['category_id'] . ' ' . $checked . '> 
						<i class="input-helper"></i>' . $categories1['category_title'] . 
					'</label>
				</li>';
			
			if (!empty($categories1['children'])) {
				$str.='<ul class="sub-categories">';
				$str.= $this->loadCategoryStructure($categories1['children'],$level+1,$relation_category_id);
				$str.='</ul>';
			}											
		}
		return $str;
	}
	
    private function saveUploadedPostFiles(&$files, &$image_names) {
		 
		$tempName = $files['post_image_file_name']['tmp_name'];
		$imgSize = $files['post_image_file_name']['size'];  
		$error = $files['post_image_file_name']['error'];  
		$imgType = $files['post_image_file_name']['type'];  
		
		if($error[0] == 4){
			return true;
		}
		if(isset($tempName)){
		 
			if(!is_array($tempName)){
				
				if ( !( $imgType == "image/gif" || $imgType == "image/jpeg" || $imgType == "image/jpg" || $imgType == "image/png" ) ) {
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_BLOG_POST_FILE_EXTENSION'));
					return false;
				}
			} else {
			
				foreach($tempName as $key => $val) {
					if($val != ""){
						 		
						if ( !( $imgType[$key] == "image/gif" || $imgType[$key] == "image/jpeg" || $imgType[$key] == "image/jpg" || $imgType[$key] == "image/png" ) ) { 			
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_BLOG_POST_FILE_EXTENSION'));
							return false;						 
						}			
					}					
				}	
				 
			}
		}
		
		if(isset($imgSize)){
			if(!is_array($imgSize)){
				$sizeFile = round($imgSize/1024);
				if ($sizeFile  > 2048 || $sizeFile  < 5) {
					Message::addErrorMessage(Utilities::getLabel('M_ERROR_BLOG_POST_FILE_SIZE'));
					return false;
				}
			} else {
				foreach($imgSize as $key => $val) {
					if($val != ""){
						$sizeFile = round($val/1024);
						if ($sizeFile  > 2048 || $sizeFile  < 5 ) {
							Message::addErrorMessage(Utilities::getLabel('M_ERROR_BLOG_POST_FILE_SIZE'));
							return false;
						}
					}
				}
			}
		}
		 
        if (is_array($files['post_image_file_name']['name'])) {
            foreach ($files['post_image_file_name']['name'] as $id => $filename) {
                if (is_uploaded_file($files['post_image_file_name']['tmp_name'][$id])) {
                    $saved_image_name = '';
                    if (Utilities::saveImage($files['post_image_file_name']['tmp_name'][$id], $files['post_image_file_name']['name'][$id], $saved_image_name, 'post-images/')) {
                        $image_names[] = $saved_image_name;
                    } else {
                        Message::addErrorMessage($files['post_image_file_name']['name'][$id] . ': ' . $saved_image_name);
                    }
                }
            }
        } elseif (is_uploaded_file($files['post_image_file_name']['tmp_name'])) {
            $saved_image_name = '';
            if (Utilities::saveImage($files['post_image_file_name']['tmp_name'], $files['post_image_file_name']['name'], $saved_image_name, 'post-images/')) {
                $image_names[] = $saved_image_name;
            } else {
                Message::addErrorMessage($files['post_image_file_name']['name'] . ': ' . $saved_image_name);
            }
        }
        if (sizeof($image_names) > 0)
            return true;
        return false;
    }
    function setMainImage() {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['imgid']) && intval($post['imgid']) > 0) {
            $blog_post_id = intval($post['blog_post_id']);
            if ($this->Blogposts->setMainImage(intval($post['imgid']), $blog_post_id)) {
                $post_images = $this->Blogposts->getPostImages($blog_post_id);
                die($this->getImagesHtml($post_images, $blog_post_id, 'post'));
            }
        }
        Message::addErrorMessage('Invalid request!!');
        die(Message::getHtml());
    }
    function delete($post_id, $token = '') {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $post_id = intval($post_id);
        if ($post_id < 1) {
            Message::addErrorMessage('Invalid request!!');
            Utilities::redirectUser(Utilities::generateUrl('blogposts', ''));
        }
        if ($this->Blogposts->deletePost($post_id)) {
            Message::addMessage('Post deleted successfully.');
            Utilities::redirectUser(Utilities::generateUrl('blogposts'));
        } else {
            Message::addErrorMessage($this->Blogposts->getError());
            Utilities::redirectUser(Utilities::generateUrl('blogposts'));
        }
 
        $this->render(false, false);
    }
    protected function getImagesHtml($product_images, $product_id, $image_folder) {
        $photo_html = '';
        if (isset($product_images['imgs']) && is_array($product_images['imgs']) && sizeof($product_images['imgs']) > 0) {
            foreach ($product_images['imgs'] as $id => $img) {
                $photo_html .= '<div class="photosquare"><img alt="" src="' . Utilities::generateUrl('image', $image_folder, array('thumb', $img),CONF_WEBROOT_URL) . '"> <a class="crossLink" href="javascript:void(0)" onclick="return removeImage(this, ' . intval($id) . ');"></a>';
                if (!(isset($product_images['main_img']) && $product_images['main_img'] == $id)) {
                    $photo_html .= '<a class="linkset button small black" href="javascript:void(0)" onclick="setMainImage(this, ' . intval($id) . ', ' . intval($product_id) . ');">Set Main Image</a>';
                }
                $photo_html .= '</div>';
            }
        }
        return $photo_html;
    }
    protected function setRemoveImageData(&$img_id_str, &$saved_imgs, &$imgs_to_remove) {
        $img_ids = explode(',', $img_id_str);
        if (is_array($img_ids) && sizeof($img_ids) > 0) {
            foreach ($img_ids as $img_id) {
                if (isset($saved_imgs[$img_id]) && strlen($saved_imgs[$img_id]) > 4) {
                    $imgs_to_remove[$img_id] = $saved_imgs[$img_id];
                }
            }
            if (sizeof($imgs_to_remove) > 0) {
                return true;
            }
        }
        return false;
    }
}
