<?php $item = $item ?? null; ?>
<?php $comments = $comments ?? []; ?>
<?php $commentPolicy = $item['comment_policy'] ?? ['comments_enabled' => true, 'voting_enabled' => true, 'replies_enabled' => true, 'max_reply_depth' => 3]; ?>
<?php
$renderCommentNode = static function (array $comment, int $depth = 0) use (&$renderCommentNode, $e, $commentPolicy): string {
  $children = is_array($comment['children'] ?? null) ? $comment['children'] : [];
  $canReply = !empty($commentPolicy['replies_enabled']) && $depth < (int) ($commentPolicy['max_reply_depth'] ?? 3);
  $canVote = !empty($commentPolicy['voting_enabled']);
  ob_start();
  ?>
  <article class="comment-item comment-node comment-depth-<?= $e((string) min($depth, 6)) ?>" data-comment-id="<?= $e((string) ($comment['id'] ?? 0)) ?>">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h3><?= $e($comment['name'] ?? 'Pembaca') ?></h3>
        <p><?= $e($depth > 0 ? 'Balasan pembaca' : 'Pembaca') ?></p>
      </div>
      <span class="comment-status">Disetujui</span>
    </div>
    <p class="comment-text"><?= $e($comment['content'] ?? $comment['comment'] ?? '') ?></p>
    <div class="comment-meta-row">
      <?php if ($canVote): ?>
        <div class="comment-vote-group">
          <button type="button" class="comment-vote-btn" data-vote-up="<?= $e((string) ($comment['id'] ?? 0)) ?>">↑ <span data-vote-up-count><?= $e((string) ($comment['up_votes'] ?? 0)) ?></span></button>
          <button type="button" class="comment-vote-btn" data-vote-down="<?= $e((string) ($comment['id'] ?? 0)) ?>">↓ <span data-vote-down-count><?= $e((string) ($comment['down_votes'] ?? 0)) ?></span></button>
          <span class="comment-score" data-vote-score><?= $e((string) ($comment['score'] ?? 0)) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($canReply): ?>
        <button type="button" class="comment-reply-toggle" data-reply-toggle="<?= $e((string) ($comment['id'] ?? 0)) ?>">Balas</button>
      <?php endif; ?>
    </div>
    <?php if ($canReply): ?>
      <form class="comment-form comment-reply-form hidden" data-reply-form="<?= $e((string) ($comment['id'] ?? 0)) ?>">
        <input type="hidden" name="parent_id" value="<?= $e((string) ($comment['id'] ?? 0)) ?>" />
        <input class="input-soft" name="name" placeholder="Nama" required />
        <input class="input-soft" name="email" type="email" placeholder="Email" required />
        <textarea class="input-soft" name="comment" rows="3" placeholder="Tulis balasan..." required></textarea>
        <button type="submit" class="btn btn-primary w-fit">Kirim balasan</button>
      </form>
    <?php endif; ?>
    <?php if ($children !== []): ?>
      <div class="comment-children">
        <?php foreach ($children as $child): ?>
          <?= $renderCommentNode($child, $depth + 1) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
  <?php
  return (string) ob_get_clean();
};
?>
<?php if (!$item): ?>
  <section class="bg-stone py-16">
    <div class="article-container text-sm text-neutral-600">Berita tidak ditemukan.</div>
  </section>
<?php else: ?>
  <section class="news-detail-hero">
    <?php if (!empty($item['image'])): ?>
      <img class="news-detail-hero-img" src="<?= $e($item['image']) ?>" alt="<?= $e($item['title']) ?>" onerror="this.src='https://genbijambi.com/public/uploads/slider-1.png'">
    <?php endif; ?>
    <div class="news-detail-hero-overlay"></div>
    <div class="news-detail-hero-content article-container fade-up in-view">
      <a data-transition href="/news" class="chip chip-light mb-7">← Kembali ke News</a>
      <div class="news-detail-meta flex flex-wrap items-center gap-3 text-xs font-semibold text-white/80">
        <span class="text-white"><?= $e($item['category']) ?></span>
        <span><?= $e(substr((string) ($item['date'] ?? ''), 0, 10)) ?></span>
      </div>
      <h1 class="page-title mt-5 text-amber-100"><?= $e($item['title']) ?></h1>
      <p class="lead mt-7 text-amber-50"><?= $e($item['excerpt']) ?></p>
    </div>
  </section>
  <section class="bg-cream py-10 md:py-16">
    <article class="article-container fade-up in-view">
      <div id="news-detail-root" data-ssr="true" class="prose-soft news-detail-content">
        <?php if (!empty($item['image'])): ?>
          <img class="news-detail-inline-image" src="<?= $e($item['image']) ?>" alt="<?= $e($item['title']) ?>" loading="lazy" onerror="this.remove()">
        <?php endif; ?>
        <?= $item['content'] ?? '' ?>
      </div>
      <?php if (!empty($item['contributor_pewarta']) || !empty($item['contributor_editor'])): ?>
        <div class="news-detail-contributors mt-10 rounded-[1.5rem] border border-neutral-900/10 bg-white/80 p-5 text-sm leading-7 text-neutral-700">
          <?php if (!empty($item['contributor_pewarta'])): ?>
            <p><strong>Pewarta:</strong> <?= $e($item['contributor_pewarta']) ?></p>
          <?php endif; ?>
          <?php if (!empty($item['contributor_editor'])): ?>
            <p><strong>Editor:</strong> <?= $e($item['contributor_editor']) ?></p>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </article>
  </section>
  <section class="bg-[var(--surface-soft)] py-12 md:py-16">
    <div class="article-container fade-up in-view">
      <div class="news-engagement-grid">
        <section class="share-card">
          <p class="eyebrow">Bagikan artikel</p>
          <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950">Bantu sebarkan kabar baik GenBI Jambi.</h2>
          <div class="mt-6 flex flex-wrap gap-2">
            <button class="share-button" data-share-url="https://wa.me/?text=<?= rawurlencode($item['title']) ?>%20https://genbijambi.com/news/<?= rawurlencode($item['slug']) ?>">WhatsApp</button>
            <button class="share-button" data-share-url="https://www.facebook.com/sharer/sharer.php?u=https://genbijambi.com/news/<?= rawurlencode($item['slug']) ?>">Facebook</button>
            <button class="share-button" data-share-url="https://twitter.com/intent/tweet?text=<?= rawurlencode($item['title']) ?>&url=https://genbijambi.com/news/<?= rawurlencode($item['slug']) ?>">X</button>
            <button class="share-button" data-copy data-canonical="https://genbijambi.com/news/<?= rawurlencode($item['slug']) ?>">Copy Link</button>
          </div>
        </section>
        <section class="comment-card">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <p class="eyebrow">Komentar</p>
              <h2 class="serif mt-3 text-3xl font-semibold tracking-tight text-neutral-950">Diskusi pembaca</h2>
            </div>
            <span class="blue-badge">Moderasi aktif</span>
          </div>
          <?php if (!empty($commentPolicy['comments_enabled'])): ?>
            <form class="comment-form mt-6" id="comment-form">
              <input class="input-soft" name="name" placeholder="Nama" required />
              <input class="input-soft" name="email" type="email" placeholder="Email" required />
              <textarea class="input-soft" name="comment" rows="4" placeholder="Tulis komentar singkat..." required></textarea>
              <button type="submit" class="btn btn-primary w-fit">Kirim komentar</button>
              <p class="text-sm leading-6 text-neutral-500">Komentar akan tampil setelah disetujui admin.</p>
            </form>
          <?php else: ?>
            <div class="comment-disabled-note mt-6">Komentar dinonaktifkan untuk artikel ini.</div>
          <?php endif; ?>
          <div class="mt-7 grid gap-3" id="comments-list">
            <?php if (empty($comments)): ?>
              <div class="rounded-2xl border border-neutral-900/10 bg-white p-5 text-sm text-neutral-600">Belum ada komentar.</div>
            <?php else: ?>
              <?php foreach ($comments as $comment): ?>
                <?= $renderCommentNode($comment) ?>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>
  </section>
<?php endif; ?>
