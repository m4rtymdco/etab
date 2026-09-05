<div class="guest">
  <div class="guest-card">
    <div class="festival-strip" aria-hidden="true"></div>
    <div class="pad">
    <h1>Reset password</h1>
    <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($link = flash('reset_link')): ?><p><a href="<?= e($link) ?>">Open reset link</a></p><?php endif; ?>
    <form method="post" action="<?= e(url('forgot-password')) ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label for="email">Account email</label>
        <input id="email" name="email" type="email" required>
      </div>
      <button class="btn" type="submit">Generate reset link</button>
    </form>
    <p><a href="<?= e(url('login')) ?>">Back to sign in</a></p>
    </div>
    <div class="festival-strip" aria-hidden="true"></div>
  </div>
</div>
