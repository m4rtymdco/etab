<div class="card" style="max-width:640px">
  <form method="post" action="<?= e(url(is_admin() ? 'admin/profile' : 'judge/profile')) ?>">
    <?= csrf_field() ?>
    <div class="form-group"><label for="name">Name</label>
      <input id="name" name="name" required value="<?= e($user['name']) ?>"></div>
    <div class="form-group"><label>Email</label>
      <input value="<?= e($user['email']) ?>" disabled></div>
    <div class="form-group"><label for="phone">Phone</label>
      <input id="phone" name="phone" value="<?= e($user['phone']) ?>"></div>
    <div class="form-group"><label for="bio">Bio</label>
      <textarea id="bio" name="bio"><?= e($user['bio']) ?></textarea></div>
    <div class="form-group"><label for="password">New password</label>
      <input id="password" type="password" name="password" minlength="8"></div>
    <div class="form-group"><label for="password_confirm">Confirm password</label>
      <input id="password_confirm" type="password" name="password_confirm"></div>
    <button class="btn" type="submit">Save profile</button>
  </form>
</div>
