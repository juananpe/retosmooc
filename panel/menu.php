<ul id="menu">
  <li class="item"><a href="index.php">Inicio</a></li>
  <li class="item"><a href="answers.php">Gestionar respuestas</a></li>
  <? include ("extramenu.php") ?>
  <li class="item"><a href="index.php?action=logout">Salir (<? if (isset($_SESSION['login'])) echo $_SESSION['login']; ?>) </a></li>
</ul>
<br><br><br><br>
