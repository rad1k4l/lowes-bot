<?php
$conf = [
    "webdrive.host" => "127.0.0.1:1111",
    'cache' =>[
        'encode.type' => MD5, // MD5  BASE_64
    ],
    'google' => [
        [ 'key' => "AIzaSyC71A5JH_4X3TfBbN2glOSEarOyWb9kY5Q" , 'cx' => "009996048491022828352:zezoblhjrqm"],
//        [ 'key' => "" , 'cx' => ""],
    ],
    "amazon" => [
        "product" =>[
            "price.selectors" => [
                "#olp-upd-new > span > a",
                "#unqualified > div.a-text-center.a-spacing-mini > span",
                "div.a-text-center.a-spacing-mini > span",
                "#olp-upd-new-used > span:nth-child(1) > a",
                "#mbc > div > div > h5:nth-child(1) > span > span",
                "#sims-fbt-form > div:nth-child(3) > div > div.sims-fbt-total-price > span.a-size-medium.a-color-price.sims-fbt-total-price-value > span",
                "#comparison_price_row > td.comparison_baseitem_column > span > span.a-offscreen",
                "div > div > h5:nth-child(1) > span > span",
                "#buyNew_dpv2 > span.a-size-small.a-color-price",
                "span.a-size-small.a-color-price",
                "#tmmSwatches > ul > li > span > span:nth-child(3) > span.olp-new.olp-link > a",
                "span > div > span > div.a-row > a > span > span",
                "#olp-upd-new-freeshipping > span > a",
                "#olp-upd-new-used-shipcharge > span > a",
                "#olp-upd-new-freeshipping-threshold > span:nth-child(1) > a",
                "#olp-upd-new-shipcharge > span > a",
                "#cm_cr-product_info > div > div.a-fixed-left-grid-col.a-col-right > div > div > div.a-fixed-left-grid-col.product-info.a-col-right > div.a-row.product-price-line > span > span.a-color-price.arp-price",
                "span.a-offscreen"
            ],
        ],
    ],

];