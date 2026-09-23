<?php
declare(strict_types=1);
require_once __DIR__.'/includes/functions.php';
require_once __DIR__.'/includes/db.php';

function article_text(string $text): string
{
    $result = '';
    $offset = 0;
    preg_match_all('/\[\[([^|\]]+)\|([^\]]+)\]\]/u', $text, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[0] as $index => $match) {
        [$token, $position] = $match;
        $result .= e(substr($text, $offset, $position - $offset));
        $label = $matches[1][$index][0];
        $destination = $matches[2][$index][0];
        if (preg_match('/^[a-z0-9._-]+\.php(?:\?[a-z0-9._?=&%-]+)?$/i', $destination)) {
            $result .= '<a class="article-inline-link" href="'.e(url($destination)).'">'.e($label).'</a>';
        } else {
            $result .= e($token);
        }
        $offset = $position + strlen($token);
    }
    return $result.e(substr($text, $offset));
}

$articles = require __DIR__.'/includes/articles-v11.php';
$slug = (string) ($_GET['slug'] ?? '');
$article = $articles[$slug] ?? null;
if (!$article) {
    http_response_code(404);
    exit('Article introuvable.');
}

$articleSlugs = array_keys($articles);
$position = array_search($slug, $articleSlugs, true);
$previousSlug = $articleSlugs[($position - 1 + count($articleSlugs)) % count($articleSlugs)];
$nextSlug = $articleSlugs[($position + 1) % count($articleSlugs)];
$relatedStatement = db()->prepare("SELECT e.*, c.name AS category_name, c.slug AS category_slug, u.identity_verified, (SELECT ep.url FROM equipment_photos ep WHERE ep.equipment_id=e.id ORDER BY ep.id LIMIT 1) AS image_url FROM equipment e JOIN categories c ON c.id=e.category_id JOIN users u ON u.id=e.owner_id WHERE e.status='published' AND e.category_id=:category ORDER BY e.created_at DESC LIMIT 2");
$relatedStatement->execute(['category' => (int) $article['category_id']]);
$relatedProducts = $relatedStatement->fetchAll();
$pageTitle = $article['title'];
$pageDescription = $article['lead'];
$activePage = 'blog';
require __DIR__.'/includes/header.php';
?>
<main id="main-content">
  <div class="container breadcrumb"><a href="<?=url('blog.php')?>">Blog</a><span>/</span><span><?=e($article['type'])?></span></div>
  <article class="article-page">
    <header class="article-header"><p class="eyebrow"><?=e($article['type'])?></p><h1><?=e($article['title'])?></h1><p><?=e($article['lead'])?></p></header>
    <img class="article-cover" src="<?=asset($article['image'])?>" alt="<?=e($article['image_alt'])?>">
    <div class="article-body rich-article">
      <?php foreach ($article['sections'] as $section): ?>
        <section>
          <h2><?=e($section['title'])?></h2>
          <?php foreach ($section['paragraphs'] ?? [] as $paragraph): ?><p><?=article_text($paragraph)?></p><?php endforeach; ?>
          <?php if (!empty($section['list'])): ?><ul><?php foreach ($section['list'] as $point): ?><li><?=article_text($point)?></li><?php endforeach; ?></ul><?php endif; ?>
          <?php if (!empty($section['table'])): ?><div class="article-table-wrap"><table><?php foreach ($section['table'] as $rowIndex => $row): ?><tr><?php foreach ($row as $cell): ?><?php if ($rowIndex === 0): ?><th><?=e($cell)?></th><?php else: ?><td><?=e($cell)?></td><?php endif; ?><?php endforeach; ?></tr><?php endforeach; ?></table></div><?php endif; ?>
        </section>
      <?php endforeach; ?>
      <?php if (!empty($article['video'])): ?><aside class="article-video-callout"><p class="eyebrow">Ressource associée</p><h2>Voir l’entretien ou le test en vidéo</h2><p>Cette ressource complète l’article avec une démonstration et un retour d’expérience.</p><a class="button" href="<?=e($article['video'])?>" target="_blank" rel="noopener">Ouvrir la vidéo ↗</a></aside><?php endif; ?>
      <section class="article-faq"><p class="eyebrow">Questions fréquentes</p><h2>FAQ</h2><?php foreach ($article['faq'] as [$question, $answer]): ?><details><summary><?=e($question)?></summary><p><?=article_text($answer)?></p></details><?php endforeach; ?></section>
      <aside class="article-related"><p class="eyebrow eyebrow-light">Du conseil à l’action</p><h2>Évaluez ou trouvez le matériel cité</h2><div><a href="<?=url('simulator.php')?>">Lancer une simulation</a><a href="<?=url('catalogue-location.php?category='.(int)$article['category_id'])?>">Produits à louer concernés</a><a href="<?=url('catalogue-vente.php?category='.(int)$article['category_id'])?>">Produits à acheter concernés</a></div></aside>
      <?php if ($relatedProducts): ?><section class="article-products"><h2>Produits liés à cet article</h2><div class="product-grid"><?php foreach ($relatedProducts as $item): ?><?php require __DIR__.'/includes/product-card.php'; ?><?php endforeach; ?></div></section><?php endif; ?>
      <nav class="article-navigation" aria-label="Navigation entre les articles"><a href="<?=url('article.php?slug='.$previousSlug)?>"><span>Article précédent</span><strong><?=e($articles[$previousSlug]['title'])?></strong></a><a href="<?=url('article.php?slug='.$nextSlug)?>"><span>Article suivant</span><strong><?=e($articles[$nextSlug]['title'])?></strong></a></nav>
    </div>
  </article>
</main>
<?php require __DIR__.'/includes/footer.php'; ?>
