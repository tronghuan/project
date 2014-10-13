<?php
/**
 * Created by PhpStorm.
 * User: tronghuan
 * Date: 10/12/14
 * Time: 8:46 AM
 */
class SM_MegaMenu_Block_Menu extends Mage_Core_Block_Template{
    public function getChildMenu(){
        $listMenu = Mage::getModel('megamenu/menu')->getCollection()->getData();
        $menuData=array();
        foreach ($listMenu as $value)
        {
            $menuData['menu'][$value['menu_id']] = $value;
            $menuData['parent'][$value['parent_id']][] = $value['menu_id'];
        }

        return $menuData ;
    }
    public function isHaveChild($id, $menu){
        foreach($menu as $value){
            if($value['parent_id']==$id){
                return true;
            }
        }
        return false;
    }
    public function getMenu($parent=0, $ul=true)
    {

        $menuData = $this->getChildMenu();
        $html = "";
        if (isset($menuData['parent'][$parent])) {
            if($ul){
                if($parent==0){
                    $html .= "<ul><li><a href='".$this->getBaseUrl()."'>Home</a></li>";
                }else{
                    $html .= "<ul class='submenu one_col'>";
                }
            }
            else{
                if($parent==0){
                    $html .= "<li><a href='".$this->getBaseUrl()."'>Home</a></li>";
                }else{
                }
            }
            foreach ($menuData['parent'][$parent] as $value) {
                $cateUrl = Mage::getModel("catalog/category")->load($menuData['menu'][$value]['cate_id'])->getUrl();
                $html .= "<li>";
                if($menuData['menu'][$value]['menu_type']==2&&$menuData['menu'][$value]['parent_id']!=0){
                    $html .= "<i class='fa fa-angle-right'></i><a href='".$cateUrl."'>" . $menuData['menu'][$value]['menu_name'] . "</a>";
                    $html .= $this->getTreeCategories($menuData['menu'][$value]['cate_id']);
                }
                else if($menuData['menu'][$value]['menu_type']==2&&$menuData['menu'][$value]['parent_id']==0){
                    $html .= "<a href='".$cateUrl."'>" . $menuData['menu'][$value]['menu_name'] . "</a>";
                    $html .= "<ul class='submenu one_col'>";
                    $html .= $this->getMenu($value, false);
                    $html .= $this->getTreeCategories($menuData['menu'][$value]['cate_id'], true);
                    $html .= "</ul>";
                }
                else if($menuData['menu'][$value]['menu_type']==3){
                    if($this->isHaveChild($menuData['menu'][$value]['menu_id'], $menuData['menu']) && $menuData['menu'][$value]['parent_id']!=0){
                        $html.="<i class='fa fa-angle-right'></i>";
                    }
                    $html .= $this->getBlockLink($menuData['menu'][$value]['block_link']);
                    //$html .= "<a href='".$menuData['menu'][$value]['custom_link']."'>" . $menuData['menu'][$value]['menu_name'] . "</a>";
                    $html .= $this->getMenu($value);
                }
                else{
                    if($this->isHaveChild($menuData['menu'][$value]['menu_id'], $menuData['menu']) && $menuData['menu'][$value]['parent_id']!=0){
                        $html.="<i class='fa fa-angle-right'></i>";
                    }
                    $html .= "<a href='http://".$menuData['menu'][$value]['custom_link']."'>" . $menuData['menu'][$value]['menu_name'] . "</a>";
                    $html .= $this->getMenu($value);
                }
                $html .= "</li>";

            }
            if($ul){
                $html .= "</ul>";
            }
            else{
            }

        }
        return $html;
    }


    protected function getTreeCategories($parentId=1, $inRootMenu=false){
        $html="";
        $catModel = Mage::getModel('catalog/category');
        $allCats = $catModel->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('is_active','1')
            ->addAttributeToFilter('include_in_menu','1')
            ->addAttributeToFilter('parent_id',array('pi' => $parentId))
            ->addAttributeToSort('position', 'asc');
        //$class = ($isChild) ? "sub-cat-list" : "cat-list";
        $catsChild = $catModel->load($parentId)->getChildrenCount();
        $html .= ($catsChild>0&&!$inRootMenu) ? '<i class="fa fa-angle-right"></i><ul class="submenu one_col">':'';
        foreach($allCats as $category)
        {
            $html .= '<li><a href="'.$category->getUrl().'">'.$category->getName()."</a>";
            $subcats = $category->getChildren();
            if($subcats != ''){
                $html .= "<i class='fa fa-angle-right'></i>".$this->getTreeCategories($category->getId()/*, true*/);
            }
            $html .= '</li>';
        }
        $html .= ($catsChild>0&&!$inRootMenu) ? '</ul>':'';
        return $html;
    }

    public function getBlockLink($blockId)
    {
        $html ="";
        $html .= "<a>Content</a>";
        $html .="<div>";
        $html .= $this->getLayout()->createBlock('cms/block')->setBlockId($blockId)->toHtml();
        $html .="</div>";
        return $html;
    }
}