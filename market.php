<?php
// add, remove and empty the product into the cart
if (!empty($_GET["action"])) {
    switch ($_GET["action"]) {
        case "add":
            $_SESSION["cart_item"] = $dbObj->addProduct($_POST);
            break;
        case "remove":
            if (!empty($_SESSION["cart_item"])) {
                // echo "<pre>"; print_r([$_SESSION["cart_item"], $_GET["pid"]]); die();
                foreach ($_SESSION["cart_item"] as $item => $value) {
                    if ($_GET["pid"] == $item)
                        unset($_SESSION["cart_item"][$item]);
                        if($_GET["pid"] == 1 && isset($_SESSION["cart_item"][4])){
                            $_SESSION["cart_item"][4]["price"] = $dbObj->afterRemovedProductA($_SESSION["cart_item"][4]["quantity"]);
                        }
                    if (empty($_SESSION["cart_item"]))
                        unset($_SESSION["cart_item"]);
                }
            }
            break;
        case "empty":
            unset($_SESSION["cart_item"]);
            break;
    }
}
?>
<HTML lang="en">

<HEAD>
    <META charset="UTF-8" />
    <META http-equiv="X-UA-Compatible" content="IE=edge" />
    <META name="viewport" content="width=device-width, initial-scale=1.0" />
    <TITLE>Super Market</TITLE>
    <!-- LINK our CSS file -->
    <LINK rel="stylesheet" href="css/style.css" />
</HEAD>

<BODY>

    <SCRIPT src="https://code.jquery.com/jquery-3.5.1.min.js"></SCRIPT>
    <SCRIPT src="index.js"></SCRIPT>
    <H2 class="container text-center">Super Market</H2>
    <SECTION class="product">
        <DIV class="container">
            <H3 class="text-center">Products</H3>
            <?php
                $productList = $dbObj->runQuery("SELECT * FROM product_details LEFT JOIN special_offers ON product_details.pid = special_offers.pid");
                if (!empty($productList)) {
                    foreach ($productList as $product => $detail) {
            ?>
            <form method="post" id="<?php echo $product_array[$product]["pid"]; ?>" action="index.php?action=add">
                <input type="hidden" id="pid" name="pid" value="<?php echo $productList[$product]["pid"];  ?>" />
                <DIV class="product-box">
                    <DIV class="product-desc">
                        <H4>Product <?php echo $productList[$product]["name"]; ?></H4>
                        <P class="product-price">£<?php echo $productList[$product]["original_price"]; ?></P>
                        <?php if ($productList[$product]["dependent_pid"] >  0) { ?>
                        <P class="product-detail">
                            Specical price <b><?php echo "£" . $productList[$product]["special_price"]; ?></b> If you
                            purchase
                            with A
                        </P>
                        <?php }else{ ?>
                        <P class="product-detail">
                            Special price for quantity <b><?php echo $productList[$product]["quantity"]; ?></b> at
                            <b>£<?php echo $productList[$product]["special_price"]; ?></b>
                        </P>
                        <?php } ?>
                        <BR />
                        <input type="text" class="product-quantity" name="quantity" value="1" size="2" />
                        <input type="submit" value="Add to Cart" class="btnAddAction" name="insert" />
                    </DIV>
                </DIV>
            </FORM>
            <?php
                    }
                }
            ?>
            <DIV class="clearfix"></DIV>
        </DIV>
    </SECTION>

    <?php
        if (isset($_SESSION["cart_item"])) {
            $totalQuantity = 0;
            $totalPrice = 0;
    ?>
    <SECTION class="shopping-cart">
        <DIV class="container">
            <H3 class="text-center">Shopping Cart</H3>
            <DIV>
                <DIV class="table-area">
                    <TABLE>
                        <TBODY>
                            <TR>
                                <TH width="20%">Name</TH>
                                <TH width="20%">Code</TH>
                                <TH width="20%">Quantity</TH>
                                <TH width="20%">SKU</TH>
                                <TH width="20%">Price</TH>
                                <TH width="20%">Action</TH>
                            </TR>
                            <TR class="blank_row">
                                <TD colspan="3"></TD>
                            </TR>

                            <?php
                                foreach ($_SESSION["cart_item"] as $item) {
                            ?>
                            <TR>
                                <TD width="20%" style="text-align:center;">
                                    <STRONG><?php echo $item["name"]; ?></STRONG>
                                </TD>
                                <TD width="20%" style="text-align:center;"><?php echo $item["pid"]; ?></TD>
                                <TD width="20%" style="text-align:center;"><?php echo $item["quantity"]; ?></TD>
                                <TD width="20%" style="text-align:center;"><?php echo $item["sku"]; ?></TD>
                                <TD width="20%" style="text-align:center;"><?php echo $item["price"]; ?></TD>
                                <TD width="20%" style="text-align:center;"><a
                                        href="index.php?action=remove&pid=<?php echo $item["pid"]; ?>"
                                        class="btnRemoveAction"><img src="trash_icon.png" alt="Remove Item" /></a></TD>
                            </TR>
                            <?php
                                    $totalQuantity += $item["quantity"];
                                    $totalPrice += ($item["price"]);
                                }
                            ?>
                            <TR class="blank_row">
                                <TD colspan="3"></TD>
                            </TR>
                            <TR class="blank_row">
                                <TD colspan="3"></TD>
                            </TR>

                            <TR>
                                <TH colspan="2" align="right">Total Quantity:</TH>
                                <TH align="center"><?php echo $totalQuantity; ?></TH>
                                <TH align="Center">Total Price:</TH>
                                <TD align="center">
                                    <STRONG>£ <?php echo number_format($totalPrice, 2); ?></STRONG>
                                </TD>
                                <TD></TD>
                            </TR>
                        </TBODY>
                    </TABLE>
                </DIV>
            </DIV>
        </DIV>
    </SECTION>
    <?php
        } else {
    ?>
    <div class="no-records">
        <img src="empty_cart.png" alt="Empty Cart" />
    </div>
    <?php
        }
    ?>
</BODY>

</HTML>