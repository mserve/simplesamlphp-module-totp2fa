<?php
$this->includeAtTemplateBase('includes/header.php');

$this->data['header'] = $this->t('{totp2fa:totp2fa:otp_header}');

?>


<div id="umcLoginWrapper">
			<h1 style="text-align: center;"><?php echo htmlspecialchars($this->t('{univentiontheme:login:loginat}', array('%s' => $this->configuration->getValue('domainname', '')))); ?></h1>

		<div id="umcLoginDialog" class="umcLoginDialog">
				<div id="umcLoginLogo" class="umcLoginLogo">
					<img id="umcLoginLogo" src="/univention/js/dijit/themes/umc/images/login_logo.svg"/>
				</div>
				<div class="umcLoginFormWrapper">
					<p id="umcLoginNotices" class="umcLoginNotices" style="display: <?php echo $this->data['errorcode'] !== NULL  ? 'block' : 'none'; ?>;">
						<?php
$error_message = '';
if ($this->data['errorcode'] !== NULL) {
	$error_message .= '<b>' . htmlspecialchars($this->t('{totp2fa:errors:title_' . $this->data['errorcode'] . '}', $this->data['errorparams'])) . '.</b> <br />';
	$error_message .= htmlspecialchars($this->t('{totp2fa:errors:descr_' . $this->data['errorcode'] . '}', $this->data['errorparams']));

	echo $error_message;
	
}
?>
					</p>
<?php
if (!$this->data['failed']) 
{
    ?>             
					<form id="umcLoginForm" name="umcLoginForm" action="?" method="post" class="umcLoginForm" autocomplete="on">

						<label for="umcLoginPassword">
							<input placeholder="<?php echo htmlspecialchars($this->t('{totp2fa:totp2fa:otp}'), ENT_QUOTES); ?>" id="umcLoginPassword" name="otp" type="number" inputmode="numeric" pattern="[0-9]*" tabindex="1" autocomplete="one-time-code"/>
						</label>
<?php
    foreach ($this->data['stateparams'] as $name => $value) {
        echo '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES) . '" value="' . htmlspecialchars($value, ENT_QUOTES) . '" />';
    }

    if (array_key_exists('selectedOrg', $this->data)) {
?>
				<div class="organization">
				<span style="padding: .3em;"><?php echo htmlspecialchars($this->t('{login:organization}')); ?></span>
				<span><select name="organization" tabindex="2" disabled="disabled ">
<?php
        $selectedOrg = array_key_exists('selectedOrg', $this->data) ? $this->data['selectedOrg'] : NULL;
        foreach ($this->data['organizations'] as $orgId => $orgDesc) {
	        if (is_array($orgDesc)) {
		        $orgDesc = $this->t($orgDesc);
	        }

            if ($orgId === $selectedOrg) {
                $selected = 'selected="selected" ';
            } else {
                $selected = '';
            }

        	printf('<option %s value="%s">%s</option>', $selected, htmlspecialchars($orgId, ENT_QUOTES), htmlspecialchars($orgDesc));
        }
?>
				</select></span>
				</div>
<?php
    } /* end if (array_key_exists('selectedOrg', $this->data)) */
?>
						<input id="umcLoginSubmit" type="submit" name="submit" tabindex="3"
                                onclick="this.value='<?php echo $this->t('{totp2fa:totp2fa:processing}'); ?>';return true;"
                                value="<?php echo htmlspecialchars($this->t('{totp2fa:totp2fa:continue_button}'), ENT_QUOTES); ?>"/>
					</form>
<?php
} /* end if (!$this->data['failed']) */
?>                    
				</div>
			</div>
			<div id="umcLoginLinks"></div>
			<!-- preload the image! -->
			<img src="/univention/js/dijit/themes/umc/images/login_bg.gif" style="height: 0; width: 0;"/>
<?php

if (!empty($this->data['links'])) {
	echo '<ul class="links" style="margin-top: 2em">';
	foreach ($this->data['links'] AS $l) {
		echo '<li><a href="' . htmlspecialchars($l['href'], ENT_QUOTES) . '">' . htmlspecialchars($this->t($l['text'])) . '</a></li>';
	}
	echo '</ul>';
}
?>
		</div>
		<script type="text/javascript">
			//<!--
			require(['dojo/domReady!'], function() {
				<?php
					printf("var node = document.getElementById('%s');\n", strlen($this->data['username']) > 0 ? 'umcLoginPassword' : 'umcLoginUsername');
				?>
				if (node) {
					setTimeout(function() {
						node.focus();
					}, 0);
				}
			});
			//-->
		</script>
<?php
$this->includeAtTemplateBase('includes/footer.php');
?>


<?php
/* 
<?php
if ($this->data['errorcode'] !== null) {
    ?>
    <div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5">
        <img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png"
             class="float-l erroricon" style="margin: 15px" alt=""/>

        <h2><?php echo $this->t('{totp2fa:totp2fa:error_header}'); ?></h2>

        <p><strong><?php
            echo $this->data['errdesc']; ?></strong></p>
        
    </div>
<?php
}

?>
    <h2 style="break: both"><?php echo $this->t('{totp2fa:totp2fa:otp_header}'); ?></h2>

    <p class="logintext"><?php echo $this->t('{totp2fa:totp2fa:otp_text}'); ?></p>

    <form action="?" method="post" name="f">
        <table>
            <tr>
                <td rowspan="2" class="loginicon">
                    <img alt=""
                        src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-authentication.48x48.png" />
                </td>
                <td><label for="otp"><?php echo $this->t('{totp2fa:totp2fa:otp}'); ?></label></td>
                <td>
                    <input id="otp" type="text" name="otp" autofocus/>
                </td>
            </tr>
            <tr id="submit">
                <td class="loginicon"></td><td></td>
                <td>
                    <button class="btn"
                            onclick="this.value='<?php echo $this->t('{totp2fa:totp2fa:processing}'); ?>';
                                this.disabled=true; this.form.submit(); return true;" tabindex="6">
                        <?php echo $this->t('{totp2fa:totp2fa:continue_button}'); ?>
                    </button>
                </td>
            </tr>
        </table>
        <?php
        foreach ($this->data['stateparams'] as $name => $value) {
            echo('<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" />');
        }
        ?>
    </form>
<?php
if (!empty($this->data['links'])) {
    echo '<ul class="links" style="margin-top: 2em">';
    foreach ($this->data['links'] as $l) {
        echo '<li><a href="'.htmlspecialchars($l['href']).'">'.htmlspecialchars($this->t($l['text'])).'</a></li>';
    }
    echo '</ul>';
}
echo('<h2 class="logintext">'.$this->t('{totp2fa:totp2fa:help_header}').'</h2>');
echo('<p class="logintext">'.$this->t('{totp2fa:totp2fa:help_text}').'</p>');

$this->includeAtTemplateBase('includes/footer.php'); */
