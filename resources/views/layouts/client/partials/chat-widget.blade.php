<div class="cupo-chat-widget" id="cupoChatWidget" data-user-id="{{ auth()->id() }}"
    data-rooms-url="{{ route('chat.rooms.index') }}" data-store-room-url="{{ route('chat.rooms.store') }}"
    data-messages-template="{{ url('/chat/rooms/__ID__/messages') }}"
    data-send-template="{{ url('/chat/rooms/__ID__/messages') }}"
    data-read-template="{{ url('/chat/rooms/__ID__/read') }}" aria-live="polite">
    <div class="cupo-chat-room" id="cupoChatRoom" role="dialog" aria-label="Hộp chat">
        <div class="cupo-chat-room-header">
            <h3>Chat <span id="cupoChatRoomCount">(0)</span></h3>
            <div class="cupo-chat-room-actions">
                <button type="button" class="cupo-room-action" id="cupoChatExpand" aria-label="Mở rộng">
                    <i class="fa-solid fa-up-right-and-down-left-from-center" id="cupoIconExpand"></i>
                    <i class="fa-solid fa-down-left-and-up-right-to-center d-none" id="cupoIconCompress"></i>
                </button>
                <button type="button" class="cupo-room-action" id="cupoChatClose" aria-label="Thu nhỏ">
                    <i class="fa-regular fa-square"></i>
                </button>
            </div>
        </div>

        <div class="cupo-chat-room-body">
            <aside class="cupo-chat-sidebar">
                <div class="cupo-chat-search-row">
                    <div class="cupo-chat-search">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="cupoChatSearch" placeholder="Tìm theo tên..." aria-label="Tìm kiếm người chat">
                    </div>
                </div>
                <div class="cupo-chat-tabs" id="cupoChatTabs">
                    <button type="button" class="cupo-chat-tab active" data-filter="all">Tất cả</button>
                    <button type="button" class="cupo-chat-tab" data-filter="seller">Người bán</button>
                    <button type="button" class="cupo-chat-tab" data-filter="buyer">Khách mua</button>
                </div>
                <div class="cupo-chat-user-list" id="cupoChatUserList"></div>
            </aside>

            <section class="cupo-chat-conversation">
                <div class="cupo-chat-detail-header" id="cupoChatDetailHeader">
                    <div class="cupo-chat-detail-info">
                        <div class="cupo-chat-detail-avatar" id="cupoChatDetailAvatar">?</div>
                        <div class="cupo-chat-detail-titles">
                            <div class="cupo-chat-detail-name-row">
                                <span class="cupo-chat-detail-name" id="cupoChatDetailName">Chọn cuộc trò chuyện</span>
                                <span class="cupo-role-badge d-none" id="cupoChatDetailBadge"></span>
                            </div>
                            <div class="cupo-chat-detail-role-desc" id="cupoChatDetailSub">Vui lòng chọn hội thoại để bắt đầu</div>
                        </div>
                    </div>
                    <div class="cupo-chat-detail-actions" id="cupoChatDetailActions">
                        <a href="#" class="cupo-btn-shop-link d-none" id="cupoChatShopLink" target="_blank">
                            <i class="fa-solid fa-store me-1"></i>Xem Shop
                        </a>
                    </div>
                </div>
                <div class="cupo-chat-detail-body" id="cupoChatDetailBody">
                    <div class="cupo-chat-empty" id="cupoChatEmpty">
                        Chưa có tin nhắn. Bấm Chat ngay trên gian hàng để bắt đầu.
                    </div>
                </div>
                <form class="cupo-chat-input-form" id="cupoChatForm">
                    <div class="cupo-chat-input-row">
                        <input type="text" class="cupo-chat-input-field" id="cupoChatInput" placeholder="Nhập tin nhắn..."
                            aria-label="Nhập tin nhắn" maxlength="1000" disabled>
                        <button type="submit" class="cupo-chat-send-btn" id="cupoChatSend" aria-label="Gửi tin nhắn"
                            disabled>
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>

    <button type="button" class="cupo-chat-toggle" id="cupoChatToggle" aria-label="Mở hộp chat" aria-expanded="false">
        <span class="cupo-chat-toggle-icon">
            <i class="fa-solid fa-comment-dots"></i>
        </span>
        <span class="cupo-chat-badge is-hidden" id="cupoChatBadge">0</span>
    </button>
</div>
