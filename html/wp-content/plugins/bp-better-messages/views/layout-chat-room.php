<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$message_id = false;
if(isset($_GET['message_id'])) $message_id = intval($_GET['message_id']);
$participants = BP_Better_Messages()->functions->get_participants( $thread_id );
if($message_id){
    $stacks = BP_Better_Messages()->functions->get_stacks( $thread_id, $message_id, 'to_message');
} else {
    $stacks = BP_Better_Messages()->functions->get_stacks( $thread_id );
}

$is_in_chat_now   = ! isset( $_GET['thread_id'] );
$side_threads     = (BP_Better_Messages()->settings['combinedView'] === '1');
$user_id          = get_current_user_id();
$subject          = BP_Better_Messages()->functions->get_thread_subject($thread_id);

$can_read = true;
if( $chat_settings['only_joined_can_read'] === '1' ){
    if( ! isset($participants['users'][get_current_user_id()]) ) {
        $can_read = false;
    }

    if( current_user_can( 'manage_options') ){
        $can_read = true;
    }
}

if( $is_mini ) {
    $side_threads = false;
}

if( $is_in_chat_now ){
    $side_threads = false;
}

$hide_participants = $chat_settings['hide_participants'] === '1';
$can_moderate = current_user_can('manage_options');

if( $can_moderate ){
    $hide_participants = false;
}

?><div class="bp-messages-wrap <?php if( $is_in_chat_now ) { echo 'bp-messages-wrap-chat'; } else { echo 'bp-messages-wrap-main'; }; ?> <?php BP_Better_Messages()->functions->messages_classes($thread_id, 'chat-room'); ?>" data-thread-id="<?php esc_attr_e($thread_id); ?>" data-chat-id="<?php esc_attr_e($chat_id); ?>">

    <div class="bp-messages-threads-wrapper threads-hidden">
        <?php if( $side_threads ) {
            if( ! isset( $_REQUEST['ignore_threads'] ) ) {
                $threads = BP_Better_Messages()->functions->get_threads( $user_id ); ?>
                <div class="bp-messages-side-threads">
                    <div class="chat-header side-header">
                        <?php
                        if( BP_Better_Messages()->settings['disableNewThread'] === '0' || current_user_can('manage_options') ) {
                            echo '<a href="' . add_query_arg( 'new-message', '', BP_Better_Messages()->functions->get_link( get_current_user_id() ) ) . '" class="new-message ajax" title="'. __( 'New Thread', 'bp-better-messages' ) . '"><i class="far fa-edit" aria-hidden="true"></i></a>';
                        } ?>
                        <?php if( BP_Better_Messages()->settings['disableSearch'] === '0' ) { ?>
                            <div class="bpbm-search">
                                <form>
                                    <input title="<?php _e('Search', 'bp-better-messages'); ?>" placeholder="<?php _e('Search...', 'bp-better-messages'); ?>" type="text" name="search" value="">
                                    <span class="close"><i class="fas fa-times" aria-hidden="true"></i></span>
                                </form>
                            </div>
                        <?php } ?>
                    </div>
                    <?php
                    $extra_tabs = BP_Better_Messages()->functions->side_view_extra_tabs();
                    echo BP_Better_Messages()->functions->render_extra_tabs( $extra_tabs );
                    ?>
                    <div class="scroller scrollbar-inner threads-list-wrapper">
                        <div class="bpbm-search-results"></div>
                        <?php echo BP_Better_Messages()->functions->render_extra_tabs_content( $extra_tabs ); ?>
                        <div class="threads-list">
                            <?php
                            if ( !empty( $threads ) ) {
                                foreach ( $threads as $thread ) {
                                    echo $thread->html;
                                }
                            } ?>
                            <div class="loading-messages">
                                <div class="bounce1"></div>
                                <div class="bounce2"></div>
                                <div class="bounce3"></div>
                            </div>
                        </div>
                    </div>
                    <?php BP_Better_Messages()->functions->render_footer(); ?>
                </div>
                <?php
            } else {
                echo '<div class="bp-messages-side-threads"></div>';
            }
        }

        echo '<div class="bp-messages-column">'; ?>
        <div class="chat-header">
            <?php if( ! $is_mini && ! $is_in_chat_now ){ ?>
                <a href="<?php echo BP_Better_Messages()->functions->get_link(); ?>" class="back ajax" title="<?php _e( 'Back', 'bp-better-messages' ); ?>"><i class="fas fa-chevron-left" aria-hidden="true"></i></a>
            <?php }

            $_subject = $subject;
            if( $is_mini ) {
                $_subject = mb_strimwidth($_subject,0, 20, '...');
            }

            echo '<strong title="' . $subject . '">' . $_subject . '</strong>';
            if( $can_read && ! $hide_participants ) {
                add_action('bp_better_messages_thread_pre_header', function ($thread_id, $participants, $is_mini, $type = 'thread') {
                    if ($is_mini) return false;
                    echo ' <a href="#" class="participants" title="' . __('Participants', 'bp-better-messages') . '"><i class="fas fa-users"></i> ' . ($participants['count']) . '</a>';
                }, 10, 4);
            }

            if( ! $is_mini ){
                echo '<a href="#" class="mobileClose"><i class="fas fa-window-close"></i></a>';

                $expandingButtons = apply_filters('bp_better_messages_thread_expanding_buttons', [], $thread_id, $participants, $is_mini, 'chat-room' );
                if( count($expandingButtons) > 0 ){
                    ?>
                    <div class="expandingButtons" title="<?php _e('More', 'bp-better-messages'); ?>">
                        <i class="fas fa-ellipsis-v"></i>
                        <div class="bpbm-dropdown-menu">
                            <?php foreach( $expandingButtons as $slug => $expandingButton) {
                                echo $expandingButton;
                            } ?>
                        </div>
                    </div>
                <?php } ?>

                <?php do_action( 'bp_better_messages_thread_pre_header', $thread_id, $participants, $is_mini, 'chat-room' ); ?>

            <?php } else {
                do_action( 'bp_better_messages_thread_pre_header', $thread_id, $participants, $is_mini, 'chat-room' );
            } ?>
        </div>


        <div class="bpbm-chat-content">
            <div class="bpbm-chat-main">
                <div class="bpbm-pinned-message">
                    <?php do_action('bp_better_messages_pinned_message', $thread_id, $participants, $is_mini, 'chat-room' ); ?>
                </div>
                <?php if(! isset($_GET['mini']) && ! $hide_participants) { ?>
                <div class="participants-panel">
                    <h4><?php _e('Participants', 'bp-better-messages'); ?></h4>
                    <div class="bp-messages-user-list">
                        <div class="bp-messages-user-list-loader"><i class="fas fa-spinner fa-spin"></i></div>
                    </div>
                </div>
                <?php } ?>

                <?php do_action( 'bp_better_messages_thread_before_scroller', $thread_id, $participants, $is_mini, 'chat-room' ); ?>
                <?php $participants_meta = '';
                if( isset($participants[ 'users' ]) ) {
                    $participants_meta = ' data-users="' . implode( ',', array_keys( $participants[ 'users' ] ) ) .'" data-users-json="' . base64_encode(json_encode( $participants[ 'users' ] )) . '"';
                } ?>
                <div class="scroller scrollbar-inner thread" <?php echo $participants_meta; ?> data-id="<?php echo $thread_id; ?>"<?php do_action('bp_better_messages_thread_div', $thread_id) ?>>
                    <div class="loading-messages">
                        <div class="bounce1"></div>
                        <div class="bounce2"></div>
                        <div class="bounce3"></div>
                    </div>
                    <div class="list<?php if($can_moderate ) echo ' can-moderate'; ?>">
                        <?php
                        if( ! $can_read ) { ?>
                            <div class="empty-thread">
                                <i class="fas fa-ban"></i>
                                <span><?php esc_attr_e('Only participants can see this chat room messages', 'bp-better-messages'); ?></span>
                            </div>
                        <?php } else if(count($stacks) == 0 || ( count($stacks) == 1 && $stacks[0]['user_id'] == 0) || ( count($stacks) == 1 && $stacks[0]['messages'][0]['message'] === '<!— BBPM START THREAD —>') ) { ?>
                        <div class="empty-thread">
                            <i class="fas fa-comments"></i>
                            <span><?php esc_attr_e('Write the message to start conversation', 'bp-better-messages'); ?></span>
                        </div>
                        <?php } else {
                            foreach ( $stacks as $stack ) {
                                echo BP_Better_Messages()->functions->render_stack( $stack );
                            }
                        } ?>
                    </div>
                </div>

        <?php do_action( 'bp_better_messages_thread_after_scroller', $thread_id, $participants, $is_mini, 'chat-room' ); ?>

        <span class="writing" data-thread-id="<?php echo $thread_id; ?>" style="display: none"></span>

        <?php if( apply_filters('bp_better_messages_can_send_message', true, get_current_user_id(), $thread_id ) ) { ?>
            <div class="reply">
                <form action="" method="POST">
                    <div class="message">
                        <?php do_action( 'bp_messages_before_reply_textarea', $thread_id ); ?>
                        <textarea placeholder="<?php esc_attr_e( "Write your message", 'bp-better-messages' ); ?>" name="message" autocomplete="off"></textarea>
                        <?php do_action( 'bp_messages_after_reply_textarea', $thread_id ); ?>
                    </div>
                    <div class="send">
                        <?php do_action('bp_better_messages_before_reply_send'); ?>
                        <button type="submit" title="<?php _e( 'Send Message', 'bp-better-messages' ); ?>"><i class="fas fa-paper-plane" aria-hidden="true"></i></button>
                        <?php do_action('bp_better_messages_after_reply_send'); ?>
                    </div>
                    <input type="hidden" name="action" value="bp_messages_send_message">
                    <input type="hidden" name="message_id" value="">
                    <input type="hidden" name="thread_id" value="<?php echo $thread_id; ?>">
                    <?php wp_nonce_field( 'sendMessage_' . $thread_id ); ?>
                </form>

                <span class="clearfix"></span>

                <?php do_action( 'bp_messages_after_reply_form', $thread_id ); ?>
            </div>


            <?php do_action( 'bp_messages_after_reply_div', $thread_id ); ?>
        <?php } else {
            global $bp_better_messages_restrict_send_message;
            if( is_array($bp_better_messages_restrict_send_message) && ! empty( $bp_better_messages_restrict_send_message ) ){
                echo '<div class="reply">';
                echo '<ul class="bp-better-messages-restrict-send-message">';
                foreach( $bp_better_messages_restrict_send_message as $error ){
                    echo '<li>' . $error . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
        } ?>
            </div>

            <?php do_action( 'bp_messages_thread_main_content', $thread_id, $participants, $is_mini, 'chat-room' ); ?>
        </div>

        <div class="preloader"></div>

        <?php echo '</div></div>'; ?>

        <?php if( ! $is_mini && BP_Better_Messages()->settings['disableTapToOpen'] === '0' ){ ?>
            <div class="bp-messages-mobile-tap"><?php _e( 'Tap to open messages', 'bp-better-messages' ); ?></div>
        <?php } ?>
    </div>