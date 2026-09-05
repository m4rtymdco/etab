<div class="guest guest-login">
  <div class="diamond-bar flat login-edge top" aria-hidden="true"></div>
  <div class="login-scene" aria-hidden="true">
    <img class="login-wall" src="<?= e(asset('img/araw-digos-bg.png')) ?>" alt="">
    <div class="login-words">
      <span class="w-duyog">DUYOG</span>
      <span class="w-dasig">DASIG</span>
      <span class="w-dungog">DUNGOG</span>
      <span class="w-digos">DIGOS</span>
    </div>
    <span class="login-note n1">♪</span>
    <span class="login-note n2">♫</span>
    <span class="login-sun"></span>
  </div>
  <div class="guest-card login-glass">
    <div class="diamond-bar flat" aria-hidden="true"></div>
    <div class="pad">
      <p class="araw-kicker">26th <span>A</span><span>R</span><span>A</span><span>W</span> NG DIGOS</p>
      <h1 class="login-brand">
        <img src="<?= e(asset('img/digos-seal.png')) ?>" alt="City of Digos seal">
        eTab
      </h1>
      <p class="wordmark stacked">
        <span>Duyog</span>
        <span>Dasig</span>
        <span>Dungog</span>
        <span>Digos</span>
      </p>
      <p class="muted login-tag">Event judging tabulator</p>
      <?php if ($msg = flash('error')): ?><div class="alert alert-error"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <form method="post" action="<?= e(url('login')) ?>">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required autocomplete="username" value="<?= e(old('email')) ?>">
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password">
        </div>
        <button class="btn login-btn" type="submit">Sign in</button>
      </form>
      <p class="login-forgot"><a href="<?= e(url('forgot-password')) ?>">Forgot password?</a></p>
    </div>
    <div class="diamond-bar flat" aria-hidden="true"></div>
  </div>
  <div class="diamond-bar flat login-edge bot" aria-hidden="true"></div>
</div>
