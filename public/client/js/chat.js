document.addEventListener("DOMContentLoaded", function () {
    const widget = document.getElementById("cupoChatWidget");
    if (!widget) {
        document.addEventListener("click", function (event) {
            const trigger = event.target.closest(".js-open-chat");
            if (!trigger) {
                return;
            }
            event.preventDefault();
            window.location.href = "/login";
        });
        return;
    }

    const roomEl = document.getElementById("cupoChatRoom");
    const toggle = document.getElementById("cupoChatToggle");
    const closeBtn = document.getElementById("cupoChatClose");
    const expandBtn = document.getElementById("cupoChatExpand");
    const form = document.getElementById("cupoChatForm");
    const input = document.getElementById("cupoChatInput");
    const sendBtn = document.getElementById("cupoChatSend");
    const userList = document.getElementById("cupoChatUserList");
    const detailName = document.getElementById("cupoChatDetailName");
    const detailBody = document.getElementById("cupoChatDetailBody");
    const detailAvatar = document.getElementById("cupoChatDetailAvatar");
    const detailBadge = document.getElementById("cupoChatDetailBadge");
    const detailSub = document.getElementById("cupoChatDetailSub");
    const shopLink = document.getElementById("cupoChatShopLink");
    const tabsWrap = document.getElementById("cupoChatTabs");
    const roomCount = document.getElementById("cupoChatRoomCount");
    const badge = document.getElementById("cupoChatBadge");
    const searchInput = document.getElementById("cupoChatSearch");

    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute("content") : "";

    const roomsUrl = widget.dataset.roomsUrl;
    const storeRoomUrl = widget.dataset.storeRoomUrl;
    const messagesTemplate = widget.dataset.messagesTemplate;
    const sendTemplate = widget.dataset.sendTemplate;

    let rooms = [];
    let currentRoomId = null;
    let currentFilter = "all";
    let lastMessageId = 0;
    let pollTimer = null;
    let sending = false;

    function csrfHeaders() {
        return {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        };
    }

    let bgPollTimer = null;

    function startBgPolling() {
        stopBgPolling();
        bgPollTimer = window.setInterval(function () {
            if (!widget.classList.contains("is-open")) {
                loadRooms(true);
            }
        }, 10000);
    }

    function stopBgPolling() {
        if (bgPollTimer) {
            window.clearInterval(bgPollTimer);
            bgPollTimer = null;
        }
    }

    function setOpen(isOpen) {
        widget.classList.toggle("is-open", isOpen);
        toggle.setAttribute("aria-expanded", String(isOpen));
        if (isOpen) {
            stopBgPolling();
            loadRooms().then(function () {
                if (currentRoomId) {
                    const activeRoom = rooms.find(function (r) {
                        return String(r.id) === String(currentRoomId);
                    });
                    if (activeRoom) {
                        selectRoom(activeRoom);
                    } else if (rooms.length > 0) {
                        selectRoom(rooms[0]);
                    }
                } else if (rooms.length > 0) {
                    selectRoom(rooms[0]);
                }
            });
            startPolling();
        } else {
            stopPolling();
            startBgPolling();
        }
    }

    function startPolling() {
        stopPolling();
        pollTimer = window.setInterval(function () {
            if (!widget.classList.contains("is-open")) {
                return;
            }
            loadRooms(true);
            if (currentRoomId) {
                loadMessages(currentRoomId, true);
            }
        }, 3000);
    }

    function stopPolling() {
        if (pollTimer) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function urlFor(template, roomId) {
        return template.replace("__ID__", String(roomId));
    }

    async function fetchJson(url, options) {
        const response = await fetch(url, options);
        const data = await response.json().catch(function () {
            return {};
        });
        if (!response.ok) {
            const error = new Error(data.message || "Không thể xử lý yêu cầu chat.");
            error.status = response.status;
            throw error;
        }
        return data;
    }

    function updateBadge(totalUnread) {
        if (!badge) {
            return;
        }
        if (totalUnread > 0) {
            badge.textContent = totalUnread > 99 ? "99+" : String(totalUnread);
            badge.classList.remove("is-hidden");
        } else {
            badge.classList.add("is-hidden");
        }
    }

    function renderRooms() {
        const keyword = (searchInput.value || "").trim().toLowerCase();
        userList.innerHTML = "";

        const filtered = rooms.filter(function (room) {
            const matchesSearch = !keyword || (room.name || "").toLowerCase().includes(keyword);
            const matchesFilter = (currentFilter === "all") || (room.other_role === currentFilter);
            return matchesSearch && matchesFilter;
        });

        if (roomCount) {
            roomCount.textContent = "(" + rooms.length + ")";
        }

        if (filtered.length === 0) {
            const empty = document.createElement("div");
            empty.className = "cupo-chat-empty";
            empty.textContent = rooms.length === 0
                ? "Chưa có cuộc trò chuyện."
                : "Không tìm thấy kết quả.";
            userList.appendChild(empty);
            return;
        }

        filtered.forEach(function (room) {
            const button = document.createElement("button");
            button.type = "button";
            button.className = "cupo-chat-user" + (String(room.id) === String(currentRoomId) ? " active" : "");
            button.dataset.roomId = String(room.id);
            button.dataset.name = room.name || "";

            const avatar = document.createElement("span");
            avatar.className = "cupo-user-avatar " + (room.other_role === "buyer" ? "is-buyer" : "is-seller");
            if (room.avatar_url) {
                const img = document.createElement("img");
                img.src = room.avatar_url;
                img.alt = room.name || "";
                img.className = "cupo-user-avatar-img";
                avatar.appendChild(img);
            } else {
                avatar.textContent = room.avatar || "?";
            }

            const main = document.createElement("span");
            main.className = "cupo-user-main";

            const nameRow = document.createElement("span");
            nameRow.className = "cupo-user-name-row";

            const name = document.createElement("span");
            name.className = "cupo-user-name";
            name.textContent = room.name || "Chat";

            const roleTag = document.createElement("span");
            roleTag.className = "cupo-card-role-tag " + (room.other_role || "buyer");
            roleTag.textContent = room.other_role_label || (room.other_role === "seller" ? "Gian hàng" : "Khách mua");

            nameRow.appendChild(name);
            nameRow.appendChild(roleTag);

            const preview = document.createElement("span");
            preview.className = "cupo-user-preview";
            preview.textContent = room.preview || "";

            main.appendChild(nameRow);
            main.appendChild(preview);

            const meta = document.createElement("span");
            meta.className = "cupo-user-meta";

            const date = document.createElement("span");
            date.className = "cupo-user-date";
            date.textContent = room.date || "";
            meta.appendChild(date);

            if (room.unread_count > 0 && String(room.id) !== String(currentRoomId)) {
                const unread = document.createElement("span");
                unread.className = "cupo-user-badge";
                unread.textContent = String(room.unread_count);
                meta.appendChild(unread);
            }

            button.appendChild(avatar);
            button.appendChild(main);
            button.appendChild(meta);

            button.addEventListener("click", function () {
                selectRoom(room);
            });

            userList.appendChild(button);
        });
    }

    function appendMessage(item) {
        const row = document.createElement("div");
        row.className = "cupo-message-row " + (item.is_mine ? "outgoing" : "incoming");
        row.dataset.messageId = String(item.id);

        const wrap = document.createElement("div");
        wrap.className = "cupo-message-wrap";

        const bubble = document.createElement("div");
        bubble.className = "cupo-message-bubble";
        bubble.textContent = item.message || "";

        const time = document.createElement("div");
        time.className = "cupo-message-time";
        time.textContent = item.created_at || "";

        wrap.appendChild(bubble);
        if (item.is_mine) {
            row.appendChild(time);
            row.appendChild(wrap);
        } else {
            row.appendChild(wrap);
            row.appendChild(time);
        }
        detailBody.appendChild(row);
        detailBody.scrollTop = detailBody.scrollHeight;
    }

    function renderMessages(items, appendOnly) {
        if (!appendOnly) {
            detailBody.innerHTML = "";
            lastMessageId = 0;
            if (!items.length) {
                const empty = document.createElement("div");
                empty.className = "cupo-chat-empty";
                empty.textContent = "Hãy gửi tin nhắn đầu tiên.";
                detailBody.appendChild(empty);
                return;
            }
        }

        const emptyState = detailBody.querySelector(".cupo-chat-empty");
        if (emptyState && items.length) {
            emptyState.remove();
        }

        items.forEach(function (item) {
            if (item.id <= lastMessageId) {
                return;
            }
            appendMessage(item);
            lastMessageId = item.id;
        });
    }

    function setComposerEnabled(enabled) {
        input.disabled = !enabled;
        sendBtn.disabled = !enabled;
    }

    function selectRoom(room) {
        currentRoomId = room.id;
        lastMessageId = 0;

        if (detailName) {
            detailName.textContent = room.name || "Chat";
        }

        if (detailAvatar) {
            detailAvatar.className = "cupo-chat-detail-avatar " + (room.other_role === "buyer" ? "is-buyer" : "is-seller");
            detailAvatar.innerHTML = "";
            if (room.avatar_url) {
                const img = document.createElement("img");
                img.src = room.avatar_url;
                img.alt = room.name || "";
                detailAvatar.appendChild(img);
            } else {
                detailAvatar.textContent = room.avatar || "?";
            }
        }

        if (detailBadge) {
            detailBadge.className = "cupo-role-badge " + (room.other_role || "buyer");
            detailBadge.textContent = room.other_role_label || (room.other_role === "seller" ? "Gian hàng" : "Khách mua hàng");
            detailBadge.classList.remove("d-none");
        }

        if (detailSub) {
            if (room.other_role === "seller") {
                detailSub.textContent = "Bạn đang trao đổi với tư cách Khách mua hàng";
            } else {
                detailSub.textContent = "Khách mua hàng đang liên hệ tới Gian hàng của bạn";
            }
        }

        if (shopLink) {
            if (room.other_role === "seller" && room.shop_id) {
                shopLink.href = "/shops/" + room.shop_id;
                shopLink.classList.remove("d-none");
            } else {
                shopLink.classList.add("d-none");
            }
        }

        setComposerEnabled(true);
        if (!userList.children.length) {
            renderRooms();
        } else {
            const userButtons = userList.querySelectorAll(".cupo-chat-user");
            userButtons.forEach(function (btn) {
                btn.classList.toggle("active", btn.dataset.roomId === String(room.id));
            });
        }
        loadMessages(room.id, false);
        window.setTimeout(function () {
            if (input && !input.disabled) {
                input.focus();
            }
        }, 120);
    }

    async function loadRooms(silent) {
        try {
            const data = await fetchJson(roomsUrl, {
                headers: csrfHeaders(),
                credentials: "same-origin",
            });
            rooms = data.data || [];
            updateBadge(data.unread_count || 0);
            renderRooms();
        } catch (error) {
            if (!silent) {
                renderRooms();
            }
        }
    }

    async function loadMessages(roomId, appendOnly) {
        const after = appendOnly && lastMessageId ? "?after_id=" + lastMessageId : "";
        try {
            const data = await fetchJson(urlFor(messagesTemplate, roomId) + after, {
                headers: csrfHeaders(),
                credentials: "same-origin",
            });
            renderMessages(data.data || [], appendOnly);
        } catch (error) {
            if (!appendOnly) {
                detailBody.innerHTML = "";
                const empty = document.createElement("div");
                empty.className = "cupo-chat-empty";
                empty.textContent = error.message;
                detailBody.appendChild(empty);
            }
        }
    }

    async function openChatWithSeller(sellerId) {
        setOpen(true);
        try {
            const data = await fetchJson(storeRoomUrl, {
                method: "POST",
                headers: csrfHeaders(),
                credentials: "same-origin",
                body: JSON.stringify({ seller_id: Number(sellerId) }),
            });
            const room = data.data;
            const existingIndex = rooms.findIndex(function (item) {
                return item.id === room.id;
            });
            if (existingIndex >= 0) {
                rooms.splice(existingIndex, 1);
            }
            rooms.unshift(room);
            selectRoom(room);
        } catch (error) {
            detailBody.innerHTML = "";
            const empty = document.createElement("div");
            empty.className = "cupo-chat-empty";
            empty.textContent = error.message;
            detailBody.appendChild(empty);
            setComposerEnabled(false);
        }
    }

    toggle.addEventListener("click", function () {
        setOpen(!widget.classList.contains("is-open"));
    });

    closeBtn.addEventListener("click", function () {
        setOpen(false);
    });

    expandBtn.addEventListener("click", function () {
        const isExpanded = widget.classList.toggle("is-expanded");
        const iconExpand = document.getElementById("cupoIconExpand");
        const iconCompress = document.getElementById("cupoIconCompress");
        if (iconExpand && iconCompress) {
            iconExpand.classList.toggle("d-none", isExpanded);
            iconCompress.classList.toggle("d-none", !isExpanded);
        }
        expandBtn.setAttribute("aria-label", isExpanded ? "Thu nhỏ" : "Mở rộng");
    });

    widget.addEventListener("click", function (event) {
        event.stopPropagation();
    });

    document.addEventListener("click", function (event) {
        const trigger = event.target.closest(".js-open-chat");
        if (trigger) {
            event.preventDefault();
            const sellerId = trigger.dataset.sellerId;
            if (sellerId) {
                openChatWithSeller(sellerId);
            }
            return;
        }

        if (!widget.classList.contains("is-open")) {
            return;
        }

        const isClickInside = event.composedPath ? event.composedPath().includes(widget) : widget.contains(event.target);
        if (!isClickInside && !event.target.closest(".js-open-chat")) {
            setOpen(false);
        }
    });

    if (tabsWrap) {
        tabsWrap.addEventListener("click", function (event) {
            const tab = event.target.closest(".cupo-chat-tab");
            if (!tab) {
                return;
            }
            tabsWrap.querySelectorAll(".cupo-chat-tab").forEach(function (item) {
                item.classList.remove("active");
            });
            tab.classList.add("active");
            currentFilter = tab.dataset.filter || "all";
            renderRooms();
        });
    }

    searchInput.addEventListener("input", renderRooms);

    form.addEventListener("submit", async function (event) {
        event.preventDefault();
        if (!currentRoomId || sending) {
            return;
        }
        const message = (input.value || "").trim();
        if (!message) {
            return;
        }

        sending = true;
        try {
            const data = await fetchJson(urlFor(sendTemplate, currentRoomId), {
                method: "POST",
                headers: csrfHeaders(),
                credentials: "same-origin",
                body: JSON.stringify({ message: message }),
            });
            input.value = "";
            const emptyState = detailBody.querySelector(".cupo-chat-empty");
            if (emptyState) {
                emptyState.remove();
            }
            appendMessage(data.data);
            lastMessageId = data.data.id;
            await loadRooms(true);
        } catch (error) {
            const empty = document.createElement("div");
            empty.className = "cupo-chat-empty";
            empty.textContent = error.message;
            detailBody.appendChild(empty);
        } finally {
            sending = false;
        }
    });

    loadRooms(true);
    startBgPolling();
});
