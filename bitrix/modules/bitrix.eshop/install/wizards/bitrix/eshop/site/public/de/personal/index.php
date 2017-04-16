<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Persönlicher Bereich");
?>					
<div class="bx_page">
	<p>Im persönlichen Bereich können Sie den aktuellen Warenkorb und den Status Ihrer Bestellungen prüfen, Ihre persönlichen Informationen einsehen oder ändern, sowie Nachrichten und Newsletter abonnieren. </p>
	<div>
		<h2>Persönliche Informationen</h2>
		<ul>
			<li><a href="profile/">Registrierungsdaten ändern</a></li>
		</ul>
	</div>
	<div>
		<h2>Bestellungen</h2>
		<ul>
			<li><a href="order/">Bestellstatus anzeigen</a></li>
			<li><a href="cart/">Warenkorb anzeigen</a></li>
			<li><a href="order/?filter_history=Y">Historie der Bestellungen anzeigen</a></li>
		</ul>
	</div>
	<div>
		<h2>Abonnement</h2>
		<ul>
			<li><a href="subscribe/">Abonnement ändern</a></li>
		</ul>
	</div>
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>