<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ebook Mode</title>
    <link href="/ebookmode.css" rel="stylesheet">
  </head>
  <body>
    <form method="GET" action="/">
      <label for="url">Url:</label>
      <input type="url" name="url" id="url" value="<?= htmlspecialchars($url) ?>">
      <button type="submit">Go</button>
    </form>
    <?php if($url): ?>
      <form method="GET" action="<?= htmlspecialchars($url) ?>">
        <button type="submit">View original</button>
      </form>
    <?php endif ?>
    <?= $content ?>
    <footer>
      <?= sprintf('Memory used %sKb', memory_get_peak_usage(true)/1024) ?>  
    </footer>
  </body>
</html>