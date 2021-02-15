<?php
$this->data['header'] = $this->t('{totp2fa:totp2fa:otp_header}');
$this->includeAtTemplateBase('includes/header.php');

?>

<?php
if ($this->data['errorcode'] !== null) {
    ?>
    <div style="border-left: 1px solid #e8e8e8; border-bottom: 1px solid #e8e8e8; background: #f5f5f5">
        <img src="/<?php echo $this->data['baseurlpath']; ?>resources/icons/experience/gtk-dialog-error.48x48.png"
             class="float-l erroricon" style="margin: 15px" alt=""/>

        <h2><?php echo $this->t('{totp2fa:errors:title_' . $this->data['errorcode'] . '}'); ?></h2>

        <p><strong><?php
            echo $this->t('{totp2fa:errors:descr_' . $this->data['errorcode'] . '}') ?></strong></p>
        
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

$this->includeAtTemplateBase('includes/footer.php');
