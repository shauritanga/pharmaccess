<p>Hello {{ $name }},</p>

<p>An account has been created for you on PharmAccess Analytics.</p>

<p>
  <strong>Login:</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a><br>
  <strong>Email:</strong> {{ $email }}<br>
  <strong>Temporary password:</strong> {{ $password }}
</p>

<p>For security, please change your password after logging in:</p>
<p><a href="{{ $settingsUrl }}">Open Settings</a></p>

<p>If you did not expect this account, please contact your administrator.</p>

<p>Thanks,<br>PharmAccess Team</p>

