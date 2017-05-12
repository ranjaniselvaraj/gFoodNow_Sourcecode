<?php
class BlogcategoriesController extends BackendController {
    private $admin;
    private $admin_id = 0;
    private $arr_status;
    function __construct($model, $controller, $action) {
        parent::__construct($model, $controller, $action);
        $admin_id = Admin::getLoggedId();
        $this->arr_status = Applicationconstants::$arr_status;
        $this->Blogcategories = new Blogcategories();
        $this->canview = Admin::getAdminAccess($admin_id,BLOG_CATEGORIES);
        $this->set('canview', $this->canview);
        $this->b_crumb = new Breadcrumb();
        Syspage::addJs(array('../js/admin/jquery.tablednd_0_5.js'));
        $this->b_crumb->add("Blog Categories Management", Utilities::generateUrl("blogcategories"));
		
    }
    function default_action() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmCategory', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->set('catId', 0);
        $this->_template->render();
    }
    function listBlogCategories() {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $post = Syspage::getPostedVar();
            $page = 1;
            if (isset($post['page']) && intval($post['page']) > 0)
                $page = intval($post['page']);
            else
                $post['page'] = $page;
            if (!empty($post['category_title']) || (isset($post['category_status'])) && $post['category_status'] != '') {
                $this->set('srch', $post);
            }
            $pagesize = Settings::getSetting("CONF_DEF_ITEMS_PER_PAGE_ADMIN");
            $post['pagesize'] = $pagesize;
            $this->set('records', $this->Blogcategories->getBlogcategoriesData($post));
            $this->set('pages', $this->Blogcategories->getTotalPages());
            $this->set('page', $page);
            $this->set('start_record', ($page - 1) * $pagesize + 1);
            $end_record = $page * $pagesize;
            $total_records = $this->Blogcategories->getTotalRecords();
            if ($total_records < $end_record)
                $end_record = $total_records;
            $this->set('end_record', $end_record);
            $this->set('total_records', $total_records);
            $this->set('pagesize', $pagesize);
            $this->set('category_parent', $post['category_parent']);
            $this->_template->render(false, false);
        }
    }
    function blogchildcategories($category_id = 0) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $frm = $this->getSearchForm();
        $this->set('frmCategory', $frm);
        $this->set('catId', $category_id);
        if ($category_id > 0) {
            $bread_array = $this->Blogcategories->fetchbreadcrumbArray($category_id);
            ksort($bread_array);
            foreach ($bread_array as $key => $value) {
                $this->b_crumb->add($value, Utilities::generateUrl("blogcategories", 'blogchildcategories', array($key)));
            }
        }
        $this->b_crumb->add("List", Utilities::generateUrl("blogcategories", 'blogchildcategories', array($category_id)));
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render('false', 'false', 'blogcategories/default_action.php');
    }
    private function getSearchForm() {
        $frm = new Form('frmSearch', 'frmSearch');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('class="table_form_vertical"');
        $frm->setFieldsPerRow(4);
        $frm->setLeftColumnProperties('width="20%"');
        $frm->captionInSameCell(true);
        $frm->addTextBox('Category Title', 'category_title', '', 'category_title', '');
        $frm->addSelectBox('Category Status', 'category_status', $this->arr_status, '', '', 'Select', 'category_status');
        $frm->addHiddenField('', 'page', 1);
        $fld1 = $frm->addButton('', 'btn_cancel', 'Clear Search', '', '  onclick=location.href="' . Utilities::generateUrl("blogcategories") . '"');
        $fld = $frm->addSubmitButton('', 'btn_submit', 'Search', 'btn_submit')->attachField($fld1);
        $frm->setOnSubmit('searchBlogCatogries(this); return false;');
        return $frm;
    }
    function add($blog_category_id = 0, $parent_category_id = 0) {
        if ($this->canview != true) {
            $this->notAuthorized();
        }
        $blog_category_id = intval($blog_category_id);
        $parent_category_id = intval($parent_category_id);
        $frm = $this->getCategoryForm();
 
        if ($blog_category_id > 0) {
            if ($this->canview != true) {
                $this->notAuthorized();
            }
            if ($blog_category_id < 1) {
                dieWithError("Unauthorized Access");
            }
        }
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($post['btn_submit'])) {  
            if (!$frm->validate($post)) {
                $frm->fill($post);
                Message::addErrorMessage($frm->getValidationErrors());
            } else {
                if ($blog_category_id > 0) {
                    if ($post['category_id'] != $blog_category_id) {
                        dieWithError("Unauthorized Access");
                    }
                    $msg = 'Category updated successfully.';
                } else {
                    $msg = 'Category added successfully.';
                }
                if ($this->Blogcategories->addUpdate($post)) {
                    Message::addMessage($msg);
                   
                } else {
                    Message::addErrorMessage($this->Blogcategories->getError());
                }
				
            }
			Utilities::redirectUser(Utilities::generateUrl('blogcategories', 'blogchildcategories', array($parent_category_id)));
        }
        $this->set('frmAdd', $frm);
        $categories = $this->Blogcategories->getAllCategories($blog_category_id);
        $frm->getField('category_parent')->options = $categories;
        $blog_category = $this->Blogcategories->getBlogcategory($blog_category_id);
        $frm->fill($blog_category);
        if ($parent_category_id > 0) {
            $bread_array = $this->Blogcategories->fetchbreadcrumbArray($parent_category_id);
            ksort($bread_array);
            foreach ($bread_array as $key => $value) {
                $this->b_crumb->add($value, Utilities::generateUrl("blogcategories", 'blogchildcategories', array($key)));
            }
            $fld = $frm->getField('category_parent');
            $frm->addHiddenField('', 'category_parent', $parent_category_id);
            $fld->captionCellExtra = 'style="display:none;"';
            $fld->fldCellExtra = 'style="display:none;"';
            $fld->value = $parent_category_id;
        }
        if ($blog_category_id > 0) {
            $this->b_crumb->add("Edit Category", Utilities::generateUrl("blogcategories", "edit"));
        } else {
            $this->b_crumb->add("Add Category", Utilities::generateUrl("blogcategories", "add"));
        }
        $this->set('frmEdit', $frm);
        $this->set('breadcrumb', $this->b_crumb->output());
        $this->_template->render();
    }
    private function getCategoryForm() {
        $frm = new Form('frmCategory', 'frmCategory');
        $frm->setExtra('class="web_form"');
        $frm->setJsErrorDisplay('afterfield');
        $frm->setTableProperties('class="table_form_vertical" ');
        $frm->setLeftColumnProperties('width="25%"');
        $fld = $frm->addRequiredField('Category Title', 'category_title', '', 'category_title', 'onblur="setSeoName(this, category_seo_name)"');
        $fld->setUnique('tbl_blog_post_categories', 'category_title', 'category_id', 'category_id', 'category_id');
        $fld_cat = $frm->addRequiredField('Category SEO Name', 'category_seo_name', '', 'category_seo_name', 'onblur="setSeoName(this, category_seo_name)"');
        $fld_cat->setUnique('tbl_blog_post_categories', 'category_seo_name', 'category_id', 'category_id', 'category_id');
        $getlastDisplayOrder = $this->Blogcategories->getlastDisplayOrder();
        // $frm->addTextBox('Category Display Order', 'category_display_order', $getlastDisplayOrder, 'category_display_order');
        //$frm->addTextArea('Category Description', 'category_description', '', 'category_description');
        $frm->addSelectBox('Category Status', 'category_status', $this->arr_status, '0', '', '', 'category_status');
        $categories = $this->Blogcategories->getAllCategories();
        $frm->addSelectBox('Category Parent', 'category_parent', $categories, 0, '', 'Select', 'category_parent');
        $frm->addTextBox('Meta Title', 'meta_title', '', 'meta_title');
        $frm->addTextArea('Meta Keywords', 'meta_keywords', '', 'meta_keywords');
        $frm->addTextArea('Meta Description', 'meta_description', '', 'meta_description');
        $fld = $frm->addHtml('', '', 'Note: Meta Others are HTML meta tags, e.g &lt;meta name="example" content="example" /&gt;. These tags will not be validated, please make sure to use correct markup.');
        $frm->addTextArea('Meta Others', 'meta_others', '', 'meta_others')->attachField($fld);
        $frm->addHiddenField('', 'category_id', '', 'category_id');
        $frm->addHiddenField('', 'meta_id', '', 'meta_id');
        $frm->addSubmitButton('', 'btn_submit', 'Submit', 'btn_submit')->html_after_field = '<input type="button"  class="" value="Cancel" onclick = "cancelCategory();">';
        return $frm;
    }
    function setCatDisplayOrder($catId) {
        $post = Syspage::getPostedVar();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $catId = $post['catId'];
            if (!$this->Blogcategories->categoryDisplayOrder($post, $catId)) {
                dieJsonError($this->Blogcategories->getError());
            }
        }
    }
    function delete($category_id, $token = '') {
        if ($this->canview != true) {
            dieWithError("Unauthorized Access");
        }
        $category_id = intval($category_id);
        if ($category_id < 1) {
            dieWithError("Unauthorized Access");
        }
        if (isset($_SESSION['stc_f_category_del_token']) && $token != '' && $token === $_SESSION['stc_f_category_del_token']) {
            unset($_SESSION['stc_f_category_del_token']);
            if ($this->Blogcategories->chkPostOfCategory($category_id)) {
                if ($this->Blogcategories->deleteCategory($category_id)) {
                    Message::addMessage('Category deleted successfully.');
                    Utilities::redirectUser(Utilities::generateUrl('blogcategories'));
                } else {
                    Message::addErrorMessage($this->Blogcategories->getError());
                    Utilities::redirectUser(Utilities::generateUrl('blogcategories'));
                }
            } else {
                Message::addErrorMessage('You cannot delete this category, there are posts associated with this category.');
                Utilities::redirectUser(Utilities::generateUrl('blogcategories'));
            }
        }
        $_SESSION['stc_f_category_del_token'] = substr(md5(microtime()), 3, 15);
        $this->set('delete_link', Utilities::generateUrl('blogcategories', 'delete', array($category_id, $_SESSION['stc_f_category_del_token'])));
        $this->_template->render();
    }
}
