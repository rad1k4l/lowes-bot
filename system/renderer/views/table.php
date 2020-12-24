<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>Document</title>

    <link rel="stylesheet" href="system/framework.css" >
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" ></script>

</head>
<body>
<style>
    .bad{
        background-color: rgba(231, 82, 82, 0.767);
    }
    .good{
        background-color: rgba(89, 196, 89, 0.911);
    }
    .dark-mode{
        background-color: #343a40;
        color: white;
        transition: 500ms;
    }

</style>


<div id="up-panel" class="col-md-12 pb-3 pt-3">

    <div class="row">
        <div class="col-md-4">
            <p>Ümumi məhsul sayı : <?= $count ?></p>
            <button type="button" id="light" class="btn btn-light">Light mode</button>
            <button type="button" id ="dark" class="btn btn-dark">Dark mode</button>
        </div>
        <div class="col-md-8">
            <nav aria-label="..." >
                <ul class="pagination">
                    <?php for($i =0; $i < $pages ; $i++): ?>
                        <li class="page-item <?= $i+1 == $thisPage ? "active" : "" ?>"><a class="page-link" href="<?= $this->getFilename($i+1)?>"> <?= $i +1 ?></a></li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>
</div>

<table class="table table-bordered ">
    <thead class="thead-dark">
    <tr>
        <th scope="col">Identifikator</th>
        <th scope="col">Qiymət</th>
        <th scope="col">Brand</th>
        <th scope="col">Şəkil</th>
        <th>Amazon</th>
        <th scope="col">Şəkil</th>
        <th scope="col">Qiymət</th>
        <th scope="col">Passed</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $k => $product): ?>
        <?php
            if(!isset($product['amazon'])) continue;

            if($k <$start || $k > $end) continue;
            $cn  = count($product['amazon']);
        ?>
        <tr>
            <th scope="rowgroup" rowspan="<?= $cn+1 ?>" >AZB<?= $k+1 ?></th>
            <td scope="rowgroup" rowspan="<?= $cn+1 ?>" ><?= $product['amount'] ?></td>
            <td scope="rowgroup" rowspan="<?= $cn+1 ?>"><?= $product['brand'] ?></td>
            <td scope="rowgroup" rowspan="<?= $cn+1 ?>" ><a target="_blank" href="https://www.lowes.com<?= $product['url'] ?>"> <img height="100" src="<?= $product['img'] ?>" </a></td>
        </tr>
        <?php  foreach ($product['amazon'] as $amazon): ?>
            <tr>
                <th>=></th>
                <th><a target="_blank" href="<?= $amazon['url'] ?>"> <img alt="" height="100" src="<?= $amazon['img'] ?>" </a></th>
                <td><?= $amazon['price'] ?></td>
                <td class="<?= $amazon['price.passed'] == 0 ? "bad" : "good" ?>" ><?= $amazon['price.passed'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endforeach; ?>




    </tbody>
</table>

<script>
    $("#dark").on("click", function(){
        $("#up-panel").addClass(" dark-mode ");
        $("table").addClass(" table-dark ");
    });

    $("#light").on("click", function(){
        $("table").removeClass(" table-dark ");
        $("#up-panel").removeClass("dark-mode");
    });

</script>
</body>
</html>