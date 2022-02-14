!function (e, t) {
    if ("object" == typeof exports && "object" == typeof module) module.exports = t(require("BPBM_MediumEditor")); else if ("function" == typeof define && define.amd) define(["BPBM_MediumEditor"], t); else {
        var i = t("object" == typeof exports ? require("BPBM_MediumEditor") : e.BPBM_MediumEditor);
        for (var n in i) ("object" == typeof exports ? exports : e)[n] = i[n]
    }
}(this, function (e) {
    return function (e) {
        function t(n) {
            if (i[n]) return i[n].exports;
            var a = i[n] = {exports: {}, id: n, loaded: !1};
            return e[n].call(a.exports, a, a.exports, t), a.loaded = !0, a.exports
        }

        var i = {};
        return t.m = e, t.c = i, t.p = "", t(0)
    }([function (e, t, i) {
        "use strict";

        function n(e) {
            return e && e.__esModule ? e : {"default": e}
        }

        function a(e) {
            return e[e.length - 1]
        }

        function s(e, t) {
            var i = e.parentNode;
            o["default"].util.unwrap(e, t);
            for (var n = i.lastChild, a = n.previousSibling; a;) 3 === n.nodeType && 3 === a.nodeType && (a.textContent += n.textContent, i.removeChild(n)), n = a, a = n.previousSibling
        }

        Object.defineProperty(t, "__esModule", {value: !0}), t.BMTCMention = t.LEFT_ARROW_KEYCODE = void 0, t.unwrapForTextNode = s;
        var r = i(1), o = n(r), l = t.LEFT_ARROW_KEYCODE = 37, h = t.BMTCMention = o["default"].Extension.extend({
            name: "mention",
            extraClassName: "",
            extraActiveClassName: "",
            extraPanelClassName: "",
            extraActivePanelClassName: "",
            extraTriggerClassNameMap: {},
            extraActiveTriggerClassNameMap: {},
            tagName: "strong",
            renderPanelContent: function () {
            },
            destroyPanelContent: function () {
            },
            activeTriggerList: ["@"],
            triggerClassNameMap: {"#": "medium-editor-mention-hash", "@": "bm-medium-editor-mention-at"},
            activeTriggerClassNameMap: {
                "#": "medium-editor-mention-hash-active",
                "@": "bm-medium-editor-mention-at-active"
            },
            hideOnBlurDelay: 300,
            init: function () {
                this.initMentionPanel(), this.attachEventHandlers()
            },
            destroy: function () {
                this.detachEventHandlers(), this.destroyMentionPanel()
            },
            initMentionPanel: function () {
                var e = this.document.createElement("div");
                e.classList.add("bm-medium-editor-mention-panel"), (this.extraPanelClassName || this.extraClassName) && e.classList.add(this.extraPanelClassName || this.extraClassName), this.getEditorOption("elementsContainer").appendChild(e), this.mentionPanel = e
            },
            destroyMentionPanel: function () {
                this.mentionPanel && (this.mentionPanel.parentNode && (this.destroyPanelContent(this.mentionPanel), this.mentionPanel.parentNode.removeChild(this.mentionPanel)), delete this.mentionPanel)
            },
            attachEventHandlers: function () {
                var e = this;
                this.unsubscribeCallbacks = [];
                var t = function (t, i) {
                    var n = e[i].bind(e);
                    e.subscribe(t, n), e.unsubscribeCallbacks.push(function () {
                        e.base.unsubscribe(t, n)
                    })
                };
                null !== this.hideOnBlurDelay && void 0 !== this.hideOnBlurDelay && (t("blur", "handleBlur"), t("focus", "handleFocus")), t("editableKeyup", "handleKeyup")
            },
            detachEventHandlers: function () {
                this.hideOnBlurDelayId && clearTimeout(this.hideOnBlurDelayId), this.unsubscribeCallbacks && (this.unsubscribeCallbacks.forEach(function (e) {
                    return e()
                }), this.unsubscribeCallbacks = null)
            },
            handleBlur: function () {
                var e = this;
                null !== this.hideOnBlurDelay && void 0 !== this.hideOnBlurDelay && (this.hideOnBlurDelayId = setTimeout(function () {
                    e.hidePanel(!1)
                }, this.hideOnBlurDelay))
            },
            handleFocus: function () {
                this.hideOnBlurDelayId && (clearTimeout(this.hideOnBlurDelayId), this.hideOnBlurDelayId = null)
            },
            handleKeyup: function (e) {
                var t = o["default"].util.getKeyCode(e), i = t === o["default"].util.keyCode.SPACE;
                this.getWordFromSelection(e.target, i ? -1 : 0), !i && -1 !== this.activeTriggerList.indexOf(this.trigger) && this.word.length > 1 ? this.showPanel() : this.hidePanel(t === l)
            },
            hidePanel: function (e) {
                try {
                    this.mentionPanel.classList.remove("bm-medium-editor-mention-panel-active");
                    var t = this.extraActivePanelClassName || this.extraActiveClassName;
                    if (t && this.mentionPanel.classList.remove(t), this.activeMentionAt && (this.activeMentionAt.classList.remove(this.activeTriggerClassName), this.extraActiveTriggerClassName && this.activeMentionAt.classList.remove(this.extraActiveTriggerClassName)), this.activeMentionAt) {
                        var i = this.activeMentionAt, n = i.parentNode, r = i.previousSibling, l = i.nextSibling,
                            h = i.firstChild, d = e ? r : l, c = void 0;
                        d ? 3 !== d.nodeType ? (c = this.document.createTextNode(""), n.insertBefore(c, d)) : c = d : (c = this.document.createTextNode(""), n.appendChild(c));
                        var u = a(h.textContent), m = 0 === u.trim().length;
                        if (m) {
                            var g = h.textContent;
                            h.textContent = g.substr(0, g.length - 1), c.textContent = "" + u + c.textContent
                        } else 0 === c.textContent.length && h.textContent.length > 1 && (c.textContent = " ");
                        e ? o["default"].selection.select(this.document, c, c.length) : o["default"].selection.select(this.document, c, Math.min(c.length, 1)), h.textContent.length <= 1 && (this.base.saveSelection(), s(this.activeMentionAt, this.document), this.base.restoreSelection()), this.activeMentionAt = null
                    }
                } catch (e){}
            },
            getWordFromSelection: function (e, t) {
                function i(e, t) {
                    var n = l[e - 1];
                    return null === n || void 0 === n ? e : 0 === n.trim().length || 0 >= e || l.length < e ? e : i(e + t, t)
                }

                var n = o["default"].selection.getSelectionRange(this.document), a = n.startContainer,
                    s = n.startOffset, r = n.endContainer;
                if (a === r) {
                    var l = a.textContent;
                    this.wordStart = i(s + t, -1), this.wordEnd = i(s + t, 1) - 1, this.word = l.slice(this.wordStart, this.wordEnd), this.trigger = this.word.slice(0, 1), this.triggerClassName = this.triggerClassNameMap[this.trigger], this.activeTriggerClassName = this.activeTriggerClassNameMap[this.trigger], this.extraTriggerClassName = this.extraTriggerClassNameMap[this.trigger], this.extraActiveTriggerClassName = this.extraActiveTriggerClassNameMap[this.trigger]
                }
            },
            showPanel: function () {
                try {
                    this.mentionPanel.classList.contains("bm-medium-editor-mention-panel-active") || (this.activatePanel(), this.wrapWordInMentionAt()), this.positionPanel(), this.updatePanelContent()
                }catch (e){}
            },
            activatePanel: function () {
                this.mentionPanel.classList.add("bm-medium-editor-mention-panel-active"), (this.extraActivePanelClassName || this.extraActiveClassName) && this.mentionPanel.classList.add(this.extraActivePanelClassName || this.extraActiveClassName)
            },
            wrapWordInMentionAt: function () {
                try {
                    var e = this.document.getSelection();
                    if (e.rangeCount) {
                        var t = e.getRangeAt(0).cloneRange();
                        if (t.startContainer.parentNode.classList.contains(this.triggerClassName)) this.activeMentionAt = t.startContainer.parentNode; else {
                            var i = Math.min(this.wordEnd, t.startContainer.textContent.length);
                            t.setStart(t.startContainer, this.wordStart), t.setEnd(t.startContainer, i);
                            var n = this.document.createElement(this.tagName);
                            n.classList.add(this.triggerClassName), this.extraTriggerClassName && n.classList.add(this.extraTriggerClassName), this.activeMentionAt = n, t.surroundContents(n), e.removeAllRanges(), e.addRange(t), o["default"].selection.select(this.document, this.activeMentionAt.firstChild, this.word.length)
                        }
                        this.activeMentionAt.classList.add(this.activeTriggerClassName), this.extraActiveTriggerClassName && this.activeMentionAt.classList.add(this.extraActiveTriggerClassName)
                    }
                } catch (e) {
                    
                }
            },
            positionPanel: function () {
                var e = this.activeMentionAt.getBoundingClientRect(), t = e.bottom, i = e.left, n = e.width,
                    a = this.window, s = a.pageXOffset, r = a.pageYOffset;
                this.mentionPanel.style.top = r + t + "px", this.mentionPanel.style.left = s + i + n + "px"
            },
            updatePanelContent: function () {
                this.renderPanelContent(this.mentionPanel, this.word, this.handleSelectMention.bind(this), this)
            },
            handleSelectMention: function (e) {
                if (e) {
                    var t = this.activeMentionAt.firstChild;
                    t.textContent = e, o["default"].selection.select(this.document, t, e.length);
                    var i = this.base.getFocusedElement();
                    i && this.base.events.updateInput(i, {target: i, currentTarget: i}), this.hidePanel(!1)
                } else this.hidePanel(!1)
            }
        });
        t["default"] = h
    }, function (t, i) {
        t.exports = e
    }])
});