<?php
function wp_postnow_admin_page() {
    $wp_postnow_domain = str_replace('http://www.', '', get_bloginfo('url'));
    $wp_postnow_next_schedule = wp_next_scheduled( 'wp_postnow_cron' ) + ( get_option( 'gmt_offset' ) ) * 3600;
    $wp_postnow_check_send_interval = get_option('wp_postnow_csi');
    $wp_postnow_next_time = explode(":", date('H:i', $wp_postnow_next_schedule));
    $post_flag = isset($_POST['posted']) ? TRUE : FALSE;
    
    if( $post_flag ){
        //validation
        $valid_flag = FALSE;
        $pattern = '/^[-+.\\w]+@[-a-z0-9]+(\\.[-a-z0-9]+)*\\.[a-z]{2,6}$/i';
        
        if(!empty($_POST['mailfrom'])){
            if(ctype_alnum($_POST['mailfrom'])){
                $_POST['mailfrom'] = strtolower($_POST['mailfrom']);
                $valid_flag = TRUE;
            }else{
                $valid_flag = FALSE;
                $valid_message[] = __('Please enter only alphanumeric SenderAddress', WP_POSTNOW_DOMAIN);
            }
        }
        
        if(!empty($_POST['ccmail'])){
            if(preg_match($pattern, $_POST['ccmail'])){
                $valid_flag = TRUE;
            }else{
                $valid_flag = FALSE;
                $valid_message[] = __('Is not in the correct format of the CC', WP_POSTNOW_DOMAIN);
            }
        }        
        if(empty($_POST['mailfrom']) && empty($_POST['ccmail'])){
            $valid_flag = TRUE;  
        }
        
        $wp_postnow_csi = NULL;
        switch($_POST['check_send_interval']){
            case '1':
                $wp_postnow_csi = 'daily';
                break;
            case '2':
                $wp_postnow_csi = 'twodays';
                break;
            case '3':
                $wp_postnow_csi = 'threedays';
                break;
            case '4':
                $wp_postnow_csi = 'fourdays';
                break;
            case '5':
                $wp_postnow_csi = 'fivedays';
                break;
            case '6':
                $wp_postnow_csi = 'sixdays';
                break;
            case '7':
                $wp_postnow_csi = 'weekly';
                break;
            default:
                $wp_postnow_csi = NULL;
        }
        
        if($valid_flag){
            $wp_postnow_update_mailfrom = $_POST['mailfrom'].'@'.$wp_postnow_domain;
            update_option('wp_postnow_interval',intval($_POST['interval'] * WP_POSTNOW_UNIXTIME));
            update_option('wp_postnow_csi',intval($_POST['check_send_interval']));
            update_option('wp_postnow_futurepost', $_POST['futurepost']);
            update_option('wp_postnow_email',stripslashes($_POST['email']));
            update_option('wp_postnow_ccmail',stripslashes($_POST['ccmail']));
            update_option('wp_postnow_fromdisp',stripslashes($_POST['fromdisp']));
            update_option('wp_postnow_mailfrom',stripslashes($wp_postnow_update_mailfrom));
            update_option('wp_postnow_title',stripslashes($_POST['title']));
            update_option('wp_postnow_message',stripslashes($_POST['message']));
            if($_POST['sendtime_h'] !== $wp_postnow_next_time[0] || sprintf("%02d",$_POST['sendtime_m']) !== $wp_postnow_next_time[1] || $_POST['check_send_interval'] !== $wp_postnow_check_send_interval){
                wp_clear_scheduled_hook('wp_postnow_cron');
                $next_local_time = current_time('timestamp')+WP_POSTNOW_UNIXTIME;
                $next_local_ymd = gmdate('Y-m-d',$next_local_time);
                $next_local_ymdhms = $next_local_ymd." ".$_POST['sendtime_h'].":".sprintf("%02d",$_POST['sendtime_m']).":00";
                $next_local_timestamp = strtotime($next_local_ymdhms);
                $test_localtime = current_time('timestamp');
                wp_schedule_event( $next_local_timestamp - ( get_option( 'gmt_offset' ) ) * 3600 , $wp_postnow_csi, 'wp_postnow_cron' );
                $wp_postnow_next_schedule = wp_next_scheduled( 'wp_postnow_cron' ) + ( get_option( 'gmt_offset' ) ) * 3600;
                $wp_postnow_next_time = explode(":", date('H:i', $wp_postnow_next_schedule));
                $wp_postnow_check_send_interval = get_option('wp_postnow_csi');
            }
        }
        
    }
    
    $wp_postnow_interval = get_option('wp_postnow_interval') / WP_POSTNOW_UNIXTIME;
    $wp_postnow_futurepost = get_option('wp_postnow_futurepost');
    $wp_postnow_email = get_option('wp_postnow_email');
    $wp_postnow_ccmail = get_option('wp_postnow_ccmail');
    $wp_postnow_mailfrom = str_replace('@'.$wp_postnow_domain, '', get_option('wp_postnow_mailfrom'));
    $wp_postnow_fromdisp = get_option('wp_postnow_fromdisp');
    
    $users = get_users();
    foreach ($users as $user) {
        $username[] = $user->user_login;
        $useremail[] = $user->user_email;
    }
    
    //強制メール送信
    /*
    $now = current_time();
    wp_schedule_single_event( $now, 'wp_postnow_cron' );
    spawn_cron( $now );
    */
    
    //wp_cronスケジュール確認
    /*
    $next_schedule = wp_next_scheduled( 'wp_postnow_cron' );
    echo date('Y/m/d H:i:s', $next_schedule);
    */
    
?>

<?php if($post_flag === TRUE && $valid_flag===TRUE):?>
<div class="updated settings-error">
    <p>
        <strong><?php _e('Save the settings', WP_POSTNOW_DOMAIN); ?></strong>
    </p>
</div>
<?php elseif($post_flag === TRUE && $valid_flag === FALSE): ?>
<div class="updated settings-error">

<?php foreach($valid_message as $error_messages): ?>
    <p>
        <strong><?php echo $error_messages; ?></strong>
    </p>
<?php endforeach; ?>
    
</div>
<? endif; ?>

<div class="wrap">
    <div id="icon-options-general" class="icon32"><br /></div><h2><?php _e('WP-PostNow Settings', WP_POSTNOW_DOMAIN); ?></h2>
        <form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <input type="hidden" name="posted" value="yes">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="terms"><?php _e('Delivery terms of email alerts', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <select name="interval" >
                                <option value="7"  <?php if($wp_postnow_interval === 7){echo'selected';} ?> ><?php _e('1Week', WP_POSTNOW_DOMAIN); ?></option>
                                <option value="14" <?php if($wp_postnow_interval === 14){echo'selected';} ?> ><?php _e('2Weeks', WP_POSTNOW_DOMAIN); ?></option>
                                <option value="30" <?php if($wp_postnow_interval === 30){echo'selected';} ?> ><?php _e('1Month', WP_POSTNOW_DOMAIN); ?></option>
                                <option value="60" <?php if($wp_postnow_interval === 60){echo'selected';} ?> ><?php _e('2Months', WP_POSTNOW_DOMAIN); ?></option>
                            </select>
                            <br/>
                            <p class="description"><?php _e('Select the delivery terms of email alerts from last posted', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="check_send_interval"><?php _e('Check and Mail delivery interval', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <select name="check_send_interval" >
                                <?php for($postnowcsi=1;$postnowcsi<=7;$postnowcsi++):?>
                                <option value="<?php echo $postnowcsi; ?>"  <?php if(intval($wp_postnow_check_send_interval) === $postnowcsi){echo'selected';} ?> ><?php echo $postnowcsi; if( $postnowcsi===1 ){echo "day";}else{echo "days";}?></option>
                                <?php endfor;?>
                            </select>
                            <br/>
                            <p class="description"><?php _e('Select the check and delivery interval', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="sendtime"><?php _e('Mail delivery time', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <select name="sendtime_h" >
                                <?php for($roophour=0;$roophour<=24;$roophour++): ?>
                                <option value="<?php echo $roophour; ?>" <?php if(intval($wp_postnow_next_time[0]) === $roophour){echo "selected";}?> ><?php echo sprintf("%02d", $roophour); ?></option>
                                <?php endfor;?>
                            </select>
                            <?php echo ' : '?>
                            <select name="sendtime_m" >
                                <?php for($roopminute=0;$roopminute<=55;$roopminute=$roopminute+5): ?>
                                <option value="<?php echo $roopminute; ?>" <?php if(intval($wp_postnow_next_time[1]) === $roopminute){echo "selected";}?> ><?php echo sprintf("%02d", $roopminute); ?></option>
                                <?php endfor;?>
                            </select>
                            &nbsp;&nbsp;
                            <strong><?php _e('Next scheduled : ',WP_POSTNOW_DOMAIN);?><?php echo date('Y/m/d H:i:s', $wp_postnow_next_schedule);?>&nbsp;<?php _e("(Once in the ",WP_POSTNOW_DOMAIN); ?><?php echo $wp_postnow_check_send_interval ?><?php _e("day(s))",WP_POSTNOW_DOMAIN);?></strong>
                            <br/>
                            <p class="description"><?php _e('Select the delivery time of email alerts', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="futurepost"><?php _e('Set the FuturePosts', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <input type="radio" name="futurepost" value="TRUE" <?php if($wp_postnow_futurepost === 'TRUE'){echo 'checked';}?> ><?php _e('Include to check for FuturePosts', WP_POSTNOW_DOMAIN); ?>&nbsp;&nbsp;
                            <input type="radio" name="futurepost" value="FALSE" <?php if($wp_postnow_futurepost === 'FALSE'){echo 'checked';}?> ><?php _e('Not included to check for FuturePosts', WP_POSTNOW_DOMAIN); ?>
                            <br/>
                            <p class="description"><?php _e('Select the setting of FuturePosts', WP_POSTNOW_DOMAIN); ?></p>
                            <p class="description"><?php _e('(Please note alert mail will not be sent if FuturePosts to be included in the check for FuturePosts until published)', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="email"><?php _e('Send mail user', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <select name="email" >
<?php                       foreach($username as $key => $val){ ?>
                                <option value="<?php echo $useremail[$key];?>" <?php if($wp_postnow_email === $useremail[$key]){echo 'selected';}?> ><?php echo $val; ?></option>
<?php                       } ?>
                            </select>
                            <br/>
                            <p class="description"><?php _e('Select the SendMailUser of e-mail alert', WP_POSTNOW_DOMAIN); ?>。</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="ccmail"><?php _e('Carbon copy mail', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <input name="ccmail" type="text" id="ccmail" value="<?php echo $wp_postnow_ccmail; ?>" class="regular-text code" /><br />
                            <p class="description"><?php _e('Enter the CC address', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="fromdisp"><?php _e('Mail sender display name', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <input name="fromdisp" type="text" id="fromdisp" value="<?php echo $wp_postnow_fromdisp; ?>" class="regular-text code" /><br />
                            <p class="description"><?php _e('Enter the mail sender display name', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="mailfrom"><?php _e('Sender address', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <input name="mailfrom" type="text" id="title" value="<?php echo $wp_postnow_mailfrom; ?>" class="regular-text code" />@<?php echo $wp_postnow_domain?><br />
                            <p class="description"><?php _e('Enter the alphanumeric SenderAddress', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="title"><?php _e('Subject', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <input name="title" type="text" id="mailfrom" value="<?php echo wp_specialchars(get_option('wp_postnow_title')); ?>" class="regular-text code" /><br />
                            <p class="description"><?php _e('Enter the subject', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="terms"><?php _e('Body', WP_POSTNOW_DOMAIN); ?><label>
                        </th>
                        <td>
                            <textarea name='message' id='message' cols='50' rows='10'><?php echo wp_specialchars(get_option('wp_postnow_message')); ?></textarea><br />
                            <p class="description"><?php _e('Enter the mail body', WP_POSTNOW_DOMAIN); ?></p>
                        </td>
                    </tr>
                </table>

            <p class="submit">
                <input type="submit" name="Submit" class="button-primary" value="<?php _e('SavingChanges', WP_POSTNOW_DOMAIN); ?>" />
            </p>
    </form>
</div>

<?php } ?>