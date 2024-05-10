class w3_loadscripts {
	constructor(e) {
		this.triggerEvents = e, this.eventOptions = {
			passive: !0
		}, this.userEventListener = this.triggerListener.bind(this),this.lazy_trigger,this.style_load_fired,this.lazy_scripts_load_fired=0,this.scripts_load_fired=0,this.scripts_load_fire=0,this.excluded_js=w3_excluded_js,this.w3_lazy_load_js=w3_lazy_load_js,this.w3_fonts = (typeof w3_googlefont != "undefined" ? w3_googlefont : []), this.w3_styles=[],this.w3_scripts = {
			normal: [],
			async: [],
			defer: [],
			lazy:[]
		}, this.allJQueries = []
	}
	user_events_add(e) {
		this.triggerEvents.forEach((t => window.addEventListener(t, e.userEventListener, e.eventOptions)))
	}
	user_events_remove(e) {
		this.triggerEvents.forEach((t => window.removeEventListener(t, e.userEventListener, e.eventOptions)))
	}
	triggerListener_on_load(){
		"loading" === document.readyState ? document.addEventListener("DOMContentLoaded", this.load_resources.bind(this)) : this.load_resources()
	}
	triggerListener() {
		this.user_events_remove(this),this.lazy_scripts_load_fired=1, this.add_html_class("w3_user"), "loading" === document.readyState ?  (document.addEventListener("DOMContentLoaded", this.load_style_resources.bind(this)),(!this.scripts_load_fire ? document.addEventListener("DOMContentLoaded", this.load_resources.bind(this)) : "")) : (this.load_style_resources(),(!this.scripts_load_fire ? this.load_resources() : "" ))
	}
	async load_style_resources(){
		if(this.style_load_fired){
			return;
		}
		this.style_load_fired=!0,this.register_styles(),document.getElementsByTagName('html')[0].setAttribute('data-css',this.w3_styles.length),document.getElementsByTagName('html')[0].setAttribute('data-css-loaded',0),this.preload_scripts(this.w3_styles), this.load_styles_preloaded()
	}
	async load_styles_preloaded(){
		setTimeout(function($this){
			if(document.getElementsByTagName('html')[0].getAttribute("css-preloaded") == 1){
				$this.load_styles($this.w3_styles);
			}else{
				$this.load_styles_preloaded();
			}
		},200,this);
	}
	async load_resources() {
		if(this.scripts_load_fired){
			return;
		}
		this.scripts_load_fired=!0, this.hold_event_listeners(), this.exe_document_write(), this.register_scripts(), this.add_html_class("w3_start"),(typeof(w3_events_on_start_js) == "function" ?	w3_events_on_start_js() : ""),this.preload_scripts(this.w3_scripts.normal), this.preload_scripts(this.w3_scripts.defer), this.preload_scripts(this.w3_scripts.async), await this.load_scripts(this.w3_scripts.normal), await this.load_scripts(this.w3_scripts.defer), await this.load_scripts(this.w3_scripts.async), await this.execute_domcontentloaded(), await this.execute_window_load(), window.dispatchEvent(new Event("w3-scripts-loaded")),this.add_html_class("w3_js"),(typeof(w3_events_on_end_js) == "function" ?	w3_events_on_end_js() : "");
		this.lazy_trigger = setInterval(this.w3_trigger_lazy_script, 500,this);
	}
	async w3_trigger_lazy_script(e) {
		if(e.lazy_scripts_load_fired){
			await e.load_scripts(e.w3_scripts.lazy),e.add_html_class("jsload");
			clearInterval(e.lazy_trigger);
		}
	}
	add_html_class(text){
		var element = document.getElementsByTagName("html")[0];
		element.classList.add(text);
	}
	register_scripts() {
		document.querySelectorAll("script[type=lazyload_int]").forEach((e => {
			e.hasAttribute("data-src") ? e.hasAttribute("async") && !1 !== e.async ? this.w3_scripts.async.push(e) : e.hasAttribute("defer") && !1 !== e.defer || "module" === e.getAttribute("data-w3-type") ? this.w3_scripts.defer.push(e) : this.w3_scripts.normal.push(e) : this.w3_scripts.normal.push(e)
		}))
		document.querySelectorAll("script[type=lazyload_ext]").forEach((e => {
			this.w3_scripts.lazy.push(e)
		}))
	}
	register_styles() {
		document.querySelectorAll("link[data-href]").forEach((e => {
			this.w3_styles.push(e);
		}))
	}
	async execute_script(e) {
		return await this.repaint_frame(), new Promise((t => {
			const n = document.createElement("script");
			let r;
			[...e.attributes].forEach((e => {
				let t = e.nodeName;
				"type" !== t && "data-src" !== t && ("data-w3-type" === t && (t = "type", r = e.nodeValue), n.setAttribute(t, e.nodeValue))		
			})),e.hasAttribute("data-src") ? (n.setAttribute("src", e.getAttribute("data-src")), n.addEventListener("load", t), n.addEventListener("error", t)) : (n.text = e.text, t()), e.parentNode !== null ? e.parentNode.replaceChild(n, e) : e;
		}))
	}
	async execute_styles(e) {
		return function(e){
			e.href = e.getAttribute("data-href");
			e.rel = "stylesheet";
		}(e)
	}
	
	async load_scripts(e) {
		const t = e.shift();
		return t ? (await this.execute_script(t), this.load_scripts(e)) : Promise.resolve()
	}
	async load_styles(e) {
		const t = e.shift();
		return t ? (this.execute_styles(t), this.load_styles(e)) : "loaded";
	}
	async load_fonts(e){
		var f = document.createDocumentFragment();
		
		e.forEach((t => {
			const s = document.createElement("link");
			s.href = t;
			s.rel = "stylesheet";
			f.appendChild(s)
			
		})), setTimeout(function(){document.head.appendChild(f)},google_fonts_delay_load)
	}
	preload_scripts(resource) {
		var e = document.createDocumentFragment();
		var counter = 0;
		var $this = this;
		[...resource].forEach((t => {
			const n = t.getAttribute("data-src");
			const j = t.getAttribute("data-href");
			if (n) {
				const t = document.createElement("link");
				t.href = n, t.rel = "preload", t.as = "script", e.appendChild(t)
			}else if(j){
				const t = document.createElement("link");
				t.href = j, t.rel = "preload", t.as = "style",counter++,resource.length == counter?t.dataset.last=1:'', e.appendChild(t),t.onload=function(){
					fetch(this.href,{"mode":"no-cors"}).then(res => res.blob()).then(blob =>{
						$this.update_css_loader();
					}).catch((error) => {
						$this.update_css_loader();
					});
				};
				t.onerror=function(){
					$this.update_css_loader();
				};
			}
		})), document.head.appendChild(e)
	}
	update_css_loader(){
		document.getElementsByTagName('html')[0].setAttribute('data-css-loaded',parseInt(document.getElementsByTagName('html')[0].getAttribute('data-css-loaded'))+1);
		if(document.getElementsByTagName('html')[0].getAttribute('data-css')==document.getElementsByTagName('html')[0].getAttribute('data-css-loaded')){
			document.getElementsByTagName('html')[0].setAttribute("css-preloaded",1);
		}
	}
	hold_event_listeners() {
		let e = {};

		function t(t, n) {
			! function(t) {
				function n(n) {
					return e[t].eventsToRewrite.indexOf(n) >= 0 ? "w3-" + n : n
				}
				e[t] || (e[t] = {
					originalFunctions: {
						add: t.addEventListener,
						remove: t.removeEventListener
					},
					eventsToRewrite: []
				}, t.addEventListener = function() {
					arguments[0] = n(arguments[0]), e[t].originalFunctions.add.apply(t, arguments)
				}, t.removeEventListener = function() {
					arguments[0] = n(arguments[0]), e[t].originalFunctions.remove.apply(t, arguments)
				})
			}(t), e[t].eventsToRewrite.push(n)
		}

		function n(e, t) {
			let n = e[t];
			Object.defineProperty(e, t, {
				get: () => n || function() {},
				set(r) {
					e["w3" + t] = n = r
				}
			})
		}
		t(document, "DOMContentLoaded"), t(window, "DOMContentLoaded"), t(window, "load"), t(window, "pageshow"), t(document, "readystatechange"), n(document, "onreadystatechange"), n(window, "onload"), n(window, "onpageshow")
	}
	hold_jquery(e) {
		let t = window.jQuery;
		Object.defineProperty(window, "jQuery", {
			get: () => t,
			set(n) {
				if (n && n.fn && !e.allJQueries.includes(n)) {
					n.fn.ready = n.fn.init.prototype.ready = function(t) {
						if(typeof t != "undefined"){
							e.scripts_load_fired ? e.domReadyFired ? t.bind(document)(n) : document.addEventListener("w3-DOMContentLoaded", () => t.bind(document)(n)) : t.bind(document)(n)
							return n(document);
						}
					};
					const t = n.fn.on;
					n.fn.on = n.fn.init.prototype.on = function() {
						if ("ready" == arguments[0] ) {
							if(this[0] === document){
								if("string" != typeof arguments[1]){
									arguments[1].bind(document)(n);
								}
							}else{
								return t.apply(this, arguments), this
							}
						}
						if (this[0] === window) {
							function e(e) {
								return e.split(" ").map(e => "load" === e || 0 === e.indexOf("load.") ? "w3-jquery-load" : e).join(" ")
							}
							"string" == typeof arguments[0] || arguments[0] instanceof String ? arguments[0] = e(arguments[0]) : "object" == typeof arguments[0] && Object.keys(arguments[0]).forEach(t => {
								Object.assign(arguments[0], {
									[e(t)]: arguments[0][t]
								})[t]
							})
						}
						return t.apply(this, arguments), this
					}, e.allJQueries.push(n)
				}
				t = n
			}
		})
	}
	async execute_domcontentloaded() {
		this.domReadyFired = !0, await this.repaint_frame(), document.dispatchEvent(new Event("w3-DOMContentLoaded")), await this.repaint_frame(), window.dispatchEvent(new Event("w3-DOMContentLoaded")), await this.repaint_frame(), document.dispatchEvent(new Event("w3-readystatechange")), await this.repaint_frame(), document.w3onreadystatechange && document.w3onreadystatechange()
	}
	async execute_window_load() {
		await this.repaint_frame(), setTimeout(function(){window.dispatchEvent(new Event("w3-load"))},100), await this.repaint_frame(), window.w3onload && window.w3onload(), await this.repaint_frame(), this.allJQueries.forEach((e => e(window).trigger("w3-jquery-load"))), window.dispatchEvent(new Event("w3-pageshow")), await this.repaint_frame(), window.w3onpageshow && window.w3onpageshow()
	}
	exe_document_write() {
		const e = new Map;
		document.write = document.writeln = function(t) {
			const n = document.currentScript,
				r = document.createRange(),
				i = n.parentElement;
			let o = e.get(n);
			void 0 === o && (o = n.nextSibling, e.set(n, o));
			const a = document.createDocumentFragment();
			r.setStart(a, 0), a.appendChild(r.createContextualFragment(t)), i.insertBefore(a, o)
		}
	}
	async repaint_frame() {
		return new Promise((e => requestAnimationFrame(e)))
	}
	static execute() {
		const e = new w3_loadscripts(["keydown", "mousemove", "touchmove", "touchstart", "touchend", "wheel"]);
		e.load_fonts(e.w3_fonts);
		e.user_events_add(e)
		if(!e.excluded_js){
			e.hold_jquery(e);
		}
		if(!e.w3_lazy_load_js){
			e.scripts_load_fire = 1;
			e.triggerListener_on_load();
		}
		function w3_trigger_on_scroll(e) {
			if(document.body != null){
				var bodyRect = document.body.getBoundingClientRect();
				if (bodyRect.top < -30) {
					e.triggerListener();
				}
				clearInterval(scroll_trigger);
			}
		}
		const scroll_trigger = setInterval(w3_trigger_on_scroll, 500,e);
	}
}
if(w3_js_is_excluded){
window.addEventListener("load",function(){
setTimeout(function(){w3_loadscripts.execute();},500);
});
}else{
setTimeout(function(){w3_loadscripts.execute();},50);
}