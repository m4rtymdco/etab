<div class="guest">
  <div class="guest-card">
    <div class="festival-strip" aria-hidden="true"></div>
    <div class="pad">
    <h1>Set a new password</h1>
    <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('reset-password')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
      <div class="form-group">
        <label for="password">New password</label>
        <input id="password" name="password" type="password" minlength="8" required>
      </div>
      <div class="form-group">
        <label for="password_confirm">Confirm password</label>
        <input id="password_confirm" name="password_confirm" type="password" minlength="8" required>
      </div>
      <button class="btn" type="submit">Update password</button>
    </form>
    </div>
    <div class="festival-strip" aria-hidden="true"></div>
  </div>
</div>
