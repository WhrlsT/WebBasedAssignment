<?php
require '_base.php';


include '_head.php';
?>

<div class="blindbox-container">
    <h1 class="blindbox-topic">Blind Box</h1>

    <div id="blindbox-product-container">
        <div class="blindbox-product">
            <div class="blindbox">
                <div>
                    <a href="labubu.php">Labubu</a>
                </div>

                <div class="blindbox-pic">
                    <a href="labubu.php"><img src="/image/labubu_soymilk.jpg" alt="labubu-poster"></a>
                </div>
            </div>

            <div class="detail">
                <a href="labubu.php">Click for more details</a>
            </div>

            <div class="addtocart">
                <a href="labubu.php">
                    <button>Add To Cart</button>
                </a>
            </div>
            
            <div class="quantity-container">
                <button class="quantity-btn" onclick="decreaseQuantity1()">-</button>
                <input type="text" class="quantity-input" id="quantity1" value="1" readonly>
                <button class="quantity-btn" onclick="increaseQuantity1()">+</button>
            </div>
        </div>
        
        <div class="blindbox-product">
            <div class="blindbox">
                <div>
                    <a href="sanrio.php">Sanrio</a>
                </div>

                <div class="blindbox-pic">
                    <a href="sanrio.php"><img src="/image/sanrio_kitty.jpg" alt="sanrio-poster"></a>
                </div>
            </div>

            <div class="detail">
                <a href="sanrio.php">Click for more details</a>
            </div>

            <div class="addtocart">
                <a href="sanrio.php">
                    <button>Add To Cart</button>
                </a>
            </div>
            
            <div class="quantity-container">
                <button class="quantity-btn" onclick="decreaseQuantity2()">-</button>
                <input type="text" class="quantity-input" id="quantity2" value="1" readonly>
                <button class="quantity-btn" onclick="increaseQuantity2()">+</button>
            </div>
        </div>

        <div class="blindbox-product">
            <div class="blindbox">
                <div>
                    <a href="lol.php">League of Legends</a>
                </div>

                <div class="blindbox-pic">
                    <a href="lol.php"><img src="/image/lol_ashe.jpg" alt="lol-poster"></a>
                </div>
            </div>

            <div class="detail">
                <a href="lol.php">Click for more details</a>
            </div>

            <div class="addtocart">
                <a href="lol.php">
                    <button>Add To Cart</button>
                </a>
            </div>
            
            <div class="quantity-container">
                <button class="quantity-btn" onclick="decreaseQuantity3()">-</button>
                <input type="text" class="quantity-input" id="quantity3" value="1" readonly>
                <button class="quantity-btn" onclick="increaseQuantity3()">+</button>
            </div>
        </div>
    </div>
</div>

<?php
include '_foot.php';