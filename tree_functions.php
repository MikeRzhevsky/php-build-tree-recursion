<?php
/*
@MikeRzhevsky miker.ru@gmail.com
 */

class DB
{
    protected $link;
    static private $instance=null;

    private function __construct() //change for your connection!!!!
    {
        $this->link=mysqli_connect('localhost','user','','Tutorial');
    }

    private function __clone()
    {

    }

    static function getInstance()
    {
        if(!self::$instance){
            self::$instance = new self();
        }
        return self::$instance;

    }
    public function Query($query)
    {
        $result = mysqli_query($this->link,$query);
        if(is_bool($result)) return $result;
        $ar_result = Array();
        while ($row=mysqli_fetch_assoc($result)){
            $ar_result[]=$row;
        }
        return $ar_result;
    }
    // Builds the array lists with data from the SQL result
    public function buildMenus($query)
    {
        $menus = array(
            'items' => array(),
            'parents' => array()
        );
        $result = mysqli_query($this->link,$query);
        while ($items = mysqli_fetch_assoc($result)) {
            // Create current menus item id into array
            $menus['items'][$items['id']] = $items;
            // Creates list of all items with children
            $menus['parents'][$items['parent']][] = $items['id'];
        }
        return $menus;
    }
}

class tree
{
    static private $createTable="
    CREATE TABLE IF NOT EXISTS items (
      id INT NOT NULL AUTO_INCREMENT,
      label varchar(50) NOT NULL,
      parent varchar(11) NOT NULL DEFAULT 0,
      sort INT DEFAULT NULL,
      PRIMARY KEY (id)
    ) ENGINE=InnoDB  DEFAULT CHARSET=UTF8 ;
    ";
    static private $insertRecords="
    INSERT INTO items (id, label,  parent,sort) VALUES
    (1, 'Rasdel 1', 0, 0),
    (2, 'Rasdel 1.1',1, 0),
    (3, 'Rasdel 1.1.2',2,0),
    (4, 'Rasdel 1.1.2.1',3,0),
    (5, 'Rasdel 2',0,0),
    (6, 'Rasdel 3',0,0),
    (7, 'Rasdel 3.1',6,0);
    ";
    static private $checkRecords ="SELECT COUNT(Id) FROM items;";
    static private $getRecords =
        "SELECT id, label, parent FROM items ORDER BY parent, sort, label;";
    static public function getMenus()
    {
        $DB = DB::getinstance();
        $createTable = $DB->Query(self::$createTable);
        if($createTable==true){
            $records = $DB->Query(self::$checkRecords);
            if(mysqli_num_rows($records)==0)
            {
                $DB->Query(self::$insertRecords);
            }
            $menus = $DB->buildMenus(self::$getRecords);
        }
        return $menus ;
    }
    static public function build_tree()
    {
        $menus = tree::getMenus();
        $tree = new tree();
       // $html = $tree->createTreeView(0,$menus);
        return $tree->createTreeView(0,$menus);
    }
    public function createTreeView($parent, $menu) {
        $html = "";
        if (isset($menu['parents'][$parent])) {
            $html .= "";
            foreach ($menu['parents'][$parent] as $itemId) {
                if(!isset($menu['parents'][$itemId])) {
                    $html .= "".$menu['items'][$itemId]['label']."</br>";
                }
                if(isset($menu['parents'][$itemId])) {
                    $html .= "".$menu['items'][$itemId]['label']."</br>";
                    $html .= "*".$this->createTreeView($itemId, $menu);
                    $html .= "";
                }
            }
            $html .= "";
        }
        return $html;
    }

}

$html = tree::build_tree();

include("tree_template.php");

?>
