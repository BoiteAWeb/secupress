<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );
?>

	<div class="secupress-section-dark secupress-settings-header secupress-flex">
		<div class="secupress-col-1-3 secupress-col-logo secupress-text-center">
			<div class="secupress-logo-block">
				<div class="secupress-lb-logo">
					<?php echo secupress_get_logo( array( 'width' => 96 ), true ); ?>
				</div>
			</div>
		</div>
		<div class="secupress-col-2-3 secupress-col-text">
			<p class="secupress-text-medium">Faites partie des premiers utilisateurs de SecuPress pro et bénéficiez d'une remise exclusive</p>
			<p>Choisissez la formule qui vous convient, et dormez sur vos deux oreilles.<br>
			Votre site sera parfaitement protégé.</p>
		</div>
	</div>

	<div class="secupress-section">

		<p class="secupress-catchphrase">Améliorez votre sécurité en débloquant<br>toutes les fonctionnalités de SecuPress Pro</p>

		<p class="secupress-inline-options secupress-text-center hide-if-no-js secupress-type-yearly">
			<button type="button" class="secupress-button secupress-inline-option secupress-current" data-type="yearly">
				<?php esc_html_e( 'Yearly', 'secupress' ); ?>
			</button>
			<button type="button" class="secupress-button secupress-inline-option" data-type="monthly">
				<?php esc_html_e( 'Monthly', 'secupress' ); ?>
				<span class="secupress-tip"><?php esc_html_e( 'Coming soon', 'secupress' ) ?></span>
			</button>
		</p>

		<div id="secupress-pricing" class="secupress-pricing secupress-flex secupress-text-center">
			
			<div class="secupress-col-1-3 secupress-flex">
				<div class="secupress-price secupress-box-shadow secupress-flex-col">
					<div class="secupress-price-header">
						<p class="secupress-price-name"><?php _e( 'Lite', 'secupress' ); ?></p>
						<p class="secupress-amounts secupress-hide-monthly">
							<span class="secupress-dollars">$</span>
							<ins>39</ins>
							<del>$59</del>
						</p>
						<p class="secupress-amounts secupress-hide-yearly">
							<span class="secupress-dollars">$</span>
							<span class="price">5<small>.99</small></span>
						</p>
						<p class="secupress-price-desc secupress-hide-monthly">soit 3 mois gratuits</p>
					</div>
					<div class="secupress-price-details">
						<p class="secupress-pd-info secupress-hide-monthly">Billed per year</p>
						<p class="secupress-pd-info secupress-hide-yearly">Billed per month</p>
						<p class="secupress-pd-benefits">
							Secure &amp; Protect
							<strong>1 Website</strong>
							Forever
						</p>
					</div>
					<div class="secupress-price-cta">
						<a href="<?php echo '#'; ?>" class="secupress-button secupress-button-primary shadow">Acheter</a>
					</div>
				</div>
			</div>
			<div class="secupress-col-1-3 secupress-flex">
				<div class="secupress-price secupress-box-shadow secupress-flex-col">
					<div class="secupress-price-header">
						<p class="secupress-price-name"><?php _e( 'Standard', 'secupress' ); ?></p>
						<p class="secupress-amounts secupress-hide-monthly">
							<span class="secupress-dollars">$</span>
							<ins>129</ins>
							<del>$149</del>
						</p>
						<p class="secupress-amounts secupress-hide-yearly">
							<span class="secupress-dollars">$</span>
							<span class="price">14<small>.99</small></span>
						</p>
						<p class="secupress-price-desc secupress-hide-monthly">soit 3 mois gratuits</p>
					</div>
					<div class="secupress-price-details">
						<p class="secupress-pd-info secupress-hide-monthly">Billed per year</p>
						<p class="secupress-pd-info secupress-hide-yearly">Billed per month</p>
						<p class="secupress-pd-benefits">
							Secure &amp; Protect
							<strong>3 Websites</strong>
							Forever
						</p>
					</div>
					<div class="secupress-price-cta">
						<a href="<?php echo '#'; ?>" class="secupress-button secupress-button-primary shadow">Acheter</a>
					</div>
				</div>
			</div>
			<div class="secupress-col-1-3 secupress-flex">
				<div class="secupress-price secupress-box-shadow secupress-flex-col">
					<div class="secupress-price-header">
						<p class="secupress-price-name"><?php _e( 'Unlimited', 'secupress' ); ?></p>
						<p class="secupress-amounts secupress-hide-monthly">
							<span class="secupress-dollars">$</span>
							<ins>249</ins>
							<del>$299</del>
						</p>
						<p class="secupress-amounts secupress-hide-yearly">
							<span class="secupress-dollars">$</span>
							<span class="price">29<small>.99</small></span>
						</p>
						<p class="secupress-price-desc secupress-hide-monthly">soit 3 mois gratuits</p>
					</div>
					<div class="secupress-price-details">
						<p class="secupress-pd-info secupress-hide-monthly">Billed per year</p>
						<p class="secupress-pd-info secupress-hide-yearly">Billed per month</p>
						<p class="secupress-pd-benefits">
							Secure &amp; Protect
							<strong>Unlimited Website</strong>
							Forever
						</p>
					</div>
					<div class="secupress-price-cta">
						<a href="<?php echo '#'; ?>" class="secupress-button secupress-button-primary shadow">Acheter</a>
					</div>
				</div>
			</div>
		</div><!-- #secupress-pricing -->

		<p class="secupress-catchphrase"><?php _e( 'Included with all plans', 'secupress' ); ?></p>

		<div class="secupress-pro-crossed-offers secupress-flex secupress-text-center secupress-p2">
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-sos.png" width="66" height="66" alt="<?php esc_attr_e( 'Support', 'secupress'); ?>">
				<p>1 an de support et mise à jour<sup>*</sup></p>
			</div>
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-imagify.png" width="66" height="66" alt="Imagify">
				<p>Bonus <strong class="secupress-tertiary">30% OFF</strong> sur <strong>Imagify</strong></p>
			</div>
			<div class="secupress-col-1-3">
				<img src="<?php echo SECUPRESS_ADMIN_IMAGES_URL; ?>icon-wp-rocket.png" width="66" height="66" alt="WP Rocket">
				<p>Bonus <strong class="secupress-tertiary">20% OFF</strong> sur <strong>WP Rocket</strong></p>
			</div>
		</div>

		<?php secupress_print_pro_advantages(); ?>

		<p class="secupress-small-caracters"><sup>*</sup> Pour continuer à bénéficier du support et des mises à jour, toutes les formules doivent être renouvelées au bout d'un an (non obligatoire)</p>

	</div>