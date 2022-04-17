<?php
class Dbconfig {
	private $host = 'localhost';
	private $user = 'root';
	private $password = '';
	private $database = 'market';
	private $con;
	
	
	function __construct() {
		$this->con = $this->connectDB();
	}
	
    /**
    * function for database connection 
    *  @return  mysqli database information
    */
	function connectDB() {
		$con = mysqli_connect($this->host,$this->user,$this->password,$this->database);
		return $con;
	}
	
	  /**
     * 	function for fech run query 
     *  @return  array result set 
     */
	function runQuery($query) {
		$result = mysqli_query($this->con, $query);
		while($row=mysqli_fetch_assoc($result)) {
			$resultset[] = $row;
		}		
		if(!empty($resultset))
			return $resultset;
	}
	

	  /**
     * 	function for fech number of rows 
     *  @return  row count
     */
	function numRows($query) {
		$result  = mysqli_query($this->con, $query);
		$rowcount = mysqli_num_rows($result);
		return $rowcount;	
	}

    /**
     * Get discounted price
     * @param pvid : int 
     * @param qty : int
     * @return  float discount price : float
     */
    function getDiscountedPrice( int $pid,  int $qty) : float {

        $addedProduct = $this->runQuery("SELECT * FROM product_details LEFT JOIN special_offers ON product_details.pid = special_offers.pid WHERE product_details.pid='" . $pid . "'");
        
        for ($i = 0; $i < count($addedProduct); $i++) {
            
            if ($qty == $addedProduct[$i]['quantity']) {
                $specialPrice = $addedProduct[$i]['special_price'];
            }

            //checked in quantity greater than sepcial offer quantity 
            if ($qty >  $addedProduct[$i]['quantity']) {
                $calculatedQty =  $qty - $addedProduct[$i]['quantity'];
                if (($calculatedQty % $addedProduct[$i]['quantity']) == 0) {
                    $specialPrice =  $addedProduct[$i]['special_price'] + $this->getDiscountedPrice($addedProduct[$i]['pid'], $calculatedQty);
                } else {
                    if ($calculatedQty  >  $addedProduct[$i]['quantity']) {
                        $specialPrice =  $addedProduct[$i]['special_price'] + $this->getDiscountedPrice($addedProduct[$i]['pid'], $calculatedQty);
                    } else {
                        $specialPrice  = $addedProduct[$i]['special_price'] + ($calculatedQty *  $addedProduct[$i]['original_price']);
                    }
                }
            }
            //checked in quantity less than sepcial offer quantity 
            if ($qty < $addedProduct[$i]['quantity']) {
                $specialPrice = $addedProduct[$i]['original_price'] * $qty;
            }
        }
        return floatval($specialPrice);
    }

    /**
     * Get Prodcut D value 
     * @return array
     */
    function getProductD(){
        return $this->runQuery("SELECT * FROM product_details LEFT JOIN special_offers ON product_details.pid = special_offers.pid WHERE product_details.pid=4");
    }

    /**
     * Update Prodcut D value after removing Product A
     * @param quantity : int
     * @return totalAmount  : float
     */
    function afterRemovedProductA($quantity){
        $addedProduct = $this->getProductD();
        $totalAmount = $addedProduct[0]['original_price'] * $quantity;
        return number_format($totalAmount, 2);
    }

    function calculatePrice($dqty, $aqty, $productDData){
        if($dqty != $aqty){
            return $dqty > $aqty ? (($dqty - $aqty) * $productDData[0]['original_price']) + ($aqty * $productDData[0]['special_price']) : $dqty * $productDData[0]['special_price'];
        }
        else{
            return $dqty * $productDData[0]['special_price'];
        }
    }

    /**
     * Check Product A is present or not
     * @param quantity : int
     * @return totalAmount  : float
     */
    function checkProductA($newItems, $items, $cartData = array()){
        $productDData = $this->getProductD();
        $price = 0.00;
        if(!empty($cartData)){
            if($newItems[0]['pid'] == 1){
                if(array_key_exists(1, $cartData)){
                    $price = number_format($cartData[1]['quantity'] * $newItems[0]['original_price'], 2);
                    if(array_key_exists(4, $cartData)){
                        $newPrice = $this->calculatePrice($cartData[4]['quantity'], $cartData[1]['quantity'], $productDData);
                        $_SESSION['cart_item'][4]['price'] = number_format($newPrice, 2);
                    }
                }
                else if(array_key_exists(4, $cartData)){
                    $newPrice = $this->calculatePrice($cartData[4]['quantity'], $items[1]['quantity'], $productDData);
                    $_SESSION['cart_item'][4]['price'] = number_format($newPrice, 2);
                }
            }
            else if($newItems[0]['pid'] == 4){
                if(array_key_exists(4, $cartData)){
                    $price = number_format($cartData[4]['quantity'] * $newItems[0]['original_price'], 2);
                    if(array_key_exists(1, $cartData)){
                        $price = $this->calculatePrice($cartData[4]['quantity'], $cartData[1]['quantity'], $productDData);
                    }
                }
                else if(!array_key_exists(1, $cartData)){
                    $items[$newItems[0]['pid']]['price'] = number_format($newItems[0]['quantity'] * $newItems[0]['original_price'], 2);
                }
            }
        }
        else{
            $items[$newItems[0]['pid']]['price'] = number_format($newItems[0]['quantity'] * $newItems[0]['original_price'], 2);
        }
        return array('items' => $items, 'price' => $price);
    }

    /**
     * Update Prodcut D value after removing Product A
     * @param postData : array
     * @return _SESSION  : array
     */
    function addProduct($postData){
        if (!empty($postData['quantity'])) {
            $discount =  $this->getDiscountedPrice($postData['pid'], $postData['quantity']);
            $insertedProduct = $this->runQuery(" SELECT * FROM product_details LEFT JOIN special_offers ON product_details.pid = special_offers.pid WHERE product_details.pid='" . $postData['pid'] . "'");
            
            $items = array(
                $insertedProduct[0]['pid'] =>
                array(
                    'name' => $insertedProduct[0]['name'],
                    'sku' => $insertedProduct[0]['sku'],
                    'pid' => $insertedProduct[0]['pid'],
                    'quantity' => $postData['quantity'],
                    'price' => number_format($discount, 2)
                )
            );
            
            if (!empty($_SESSION['cart_item'])) {
                if (in_array($insertedProduct[0]['pid'], array_keys($_SESSION['cart_item']))) {
                    foreach ($_SESSION['cart_item'] as $item => $value) {
                        if ($insertedProduct[0]['pid'] == $item) {
                            if (empty($_SESSION['cart_item'][$item]['quantity'])) {
                                $_SESSION['cart_item'][$item]['quantity'] = 0;
                            }
                            $_SESSION['cart_item'][$item]['quantity'] += $postData['quantity'];
                            $price = 0;
                            if($insertedProduct[0]['pid'] == 4 || $insertedProduct[0]['pid'] == 1){
                                $price = $this->checkProductA($insertedProduct, $items, $_SESSION['cart_item'])['price'];
                            }
                            else{
                                $price = $this->getDiscountedPrice($_SESSION['cart_item'][$item]['pid'], $_SESSION['cart_item'][$item]['quantity']);
                            }
                            $_SESSION['cart_item'][$item]['price'] = number_format($price, 2);
                        }
                    }
                } else {
                    if($insertedProduct[0]['pid'] == 4 || $insertedProduct[0]['pid'] == 1){
                        $items = $this->checkProductA($insertedProduct, $items, $_SESSION['cart_item'])['items'];
                    }
                    $_SESSION['cart_item'] = $_SESSION['cart_item'] + $items;
                }
            } else {
                if($insertedProduct[0]['pid'] == 4){
                    $items = $this->checkProductA($insertedProduct, $items)['items'];
                }
                $_SESSION['cart_item'] = $items;                
            }
        }
    return $_SESSION['cart_item'];
    }
}
?>