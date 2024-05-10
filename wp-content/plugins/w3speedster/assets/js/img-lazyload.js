var w3IsMobile = (window.matchMedia("(max-width: 767px)").matches ? 1 : 0);

function w3ToWebp(elementImg) {
    for (var ig = 0; ig < elementImg.length; ig++) {
        if (elementImg[ig].getAttribute("data-src") != null && elementImg[ig].getAttribute("data-src") != "") {
            var datasrc = elementImg[ig].getAttribute("data-src");
            elementImg[ig].setAttribute("data-src", datasrc.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path));
        }
        if (elementImg[ig].getAttribute("data-srcset") != null && elementImg[ig].getAttribute("data-srcset") != "") {
            var datasrcset = elementImg[ig].getAttribute("data-srcset");
            elementImg[ig].setAttribute("data-srcset", datasrcset.replace(/w3.webp/g, "").split(w3_webp_path).join(w3_upload_path));
        }
        if (elementImg[ig].src != null && elementImg[ig].src != "") {
            var src = elementImg[ig].src;
            elementImg[ig].src = src.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path);
        }
        if (elementImg[ig].srcset != null && elementImg[ig].srcset != "") {
            var srcset = elementImg[ig].srcset;
            elementImg[ig].srcset = srcset.replace(/w3.webp/g, "").split(w3_webp_path).join(w3_upload_path);
        }
    }
}

function fixWebp() {
    if (!w3HasWebP) {
        var elementNames = ["*"];
        w3ToWebp(document.querySelectorAll("img[data-src$='w3.webp']"));
        w3ToWebp(document.querySelectorAll("img[src$='w3.webp']"));
        elementNames.forEach(function(tagName) {
            var tags = document.getElementsByTagName(tagName);
            var numTags = tags.length;
            for (var i = 0; i < numTags; i++) {
                var tag = tags[i];
                var style = tag.currentStyle || window.getComputedStyle(tag, false);
                var bg = style.backgroundImage;
                if (bg.match("w3.webp")) {
                    if (document.all) {
                        tag.style.setAttribute("cssText", ";background-image: " + bg.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path) + " !important;");
                    } else {
                        tag.setAttribute("style", tag.getAttribute("style") + ";background-image: " + bg.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path) + " !important;");
                    }
                }
            }
        });
    }
}

function w3ChangeWebp() {
    if (bg.match("w3.webp")) {
        var style1 = {};
        if (document.all) {
            tag.style.setAttribute("cssText", "background-image: " + bg.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path) + " !important");
            style1 = tag.currentStyle || window.getComputedStyle(tag, false);
        } else {
            tag.setAttribute("style", "background-image: " + bg.replace("w3.webp", "").replace(w3_webp_path, w3_upload_path) + " !important");
            style1 = tag.currentStyle || window.getComputedStyle(tag, false);
        }
    }
}
var w3HasWebP = false,
    w3Bglazyload = 1;
var img = new Image();
img.onload = function() {
    w3HasWebP = !!(img.height > 0 && img.width > 0);
};
img.onerror = function() {
    w3HasWebP = false;
    fixWebp();
};
img.src = blank_image_webp_url;

function w3_events_on_end_js() {
    w3Bglazyload = 0;
}
let w3LoadResource = {};
let lazyObserver = {};
function w3LazyLoadResource(lazyResources,resource){
	if ("IntersectionObserver" in window) {
        lazyObserver[resource] = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
					w3LoadResource[resource](entry);
                }
            });
        });
        lazyResources.forEach(function(lazyVideo) {
            lazyObserver[resource].observe(lazyVideo);
        });
    } else {
		lazyResources.forEach(function(lazyResource) {
			w3LoadResource['rl'+resource](lazyResource);
		});
    }
}
w3LoadResource['rlvideo'] = function(lazyResource){
	lazyloadVideo(lazyResource);
	delete lazyResource.dataset.class;
}
w3LoadResource['video'] = function(entry){
	let lazyVideo = entry.target;
	lazyloadVideo(lazyVideo);
	delete lazyVideo.dataset.class;
	lazyObserver['video'].unobserve(lazyVideo);
}
w3LoadResource['iframe'] = function(entry){
	let lazyIframe = entry.target;
	var elem = document.createElement("iframe");
	var index;
	for (index = lazyIframe.attributes.length - 1; index >= 0; --index) {
		elem.attributes.setNamedItem(lazyIframe.attributes[index].cloneNode());
	}
	elem.src = lazyIframe.dataset.src;
	lazyIframe.parentNode.replaceChild(elem, lazyIframe);
	delete lazyIframe.dataset.class;
	lazyObserver['iframe'].unobserve(lazyIframe);
}
w3LoadResource['rliframe'] = function(lazyResource){
	lazyResource.src = lazyResource.dataset.src ? lazyResource.dataset.src : lazyResource.src;
	delete lazyResource.dataset.class;
}
w3LoadResource['bgImg'] = function(entry){
	let lazyBg = entry.target;
	lazyBg.classList.add("w3_bg");
	lazyObserver['bgImg'].unobserve(lazyBg);
}
w3LoadResource['rlbgImg'] = function(lazyResource){
	const lazyBgStyle = document.getElementById("w3_bg_load");
	lazyBgStyle.remove();
}
w3LoadResource['img'] = function(entry){
	let lazyImage = entry.target;
	if (w3IsMobile && lazyImage.getAttribute("data-mob-src")) {
		lazyImage.src = lazyImage.getAttribute("data-mob-src");
	} else {
		lazyImage.src = lazyImage.dataset.src ? lazyImage.dataset.src : lazyImage.src;
	}
	lazyImage.srcset = lazyImage.dataset.srcset ? lazyImage.dataset.srcset : lazyImage.srcset;
	delete lazyImage.dataset.class;
	lazyObserver['img'].unobserve(lazyImage);
}
w3LoadResource['rlimg'] = function(lazyResource){
	lazyResource.src = lazyResource.dataset.src ? lazyResource.dataset.src : lazyResource.src;
	lazyResource.srcset = lazyResource.dataset.srcset ? lazyResource.dataset.srcset : lazyResource.srcset;
	delete lazyResource.dataset.class;
}
function w3_events_on_start_js() {
    var lazyvideos = document.getElementsByTagName("videolazy");
    convert_to_video_tag(lazyvideos);
    var lazyVideos = [].slice.call(document.querySelectorAll("video[data-class='LazyLoad'], audio[data-class='LazyLoad']"));
	var lazyIframes = [].slice.call(document.querySelectorAll("iframelazy[data-class='LazyLoad']"));
	w3LazyLoadResource(lazyVideos,'video');   
	w3LazyLoadResource(lazyIframes,'iframe');
}

(function() {
    var lazyImages = [].slice.call(document.querySelectorAll("img[data-class='LazyLoad']"));
    var lazyBgs = [].slice.call(document.querySelectorAll("div, section, iframelazy"));
	w3LazyLoadResource(lazyBgs,'bgImg');
	w3LazyLoadResource(lazyImages,'img');
})();

function lazyloadVideo(lazyVideo) {
    if (typeof(lazyVideo.getElementsByTagName("source")[0]) == "undefined") {
        lazyloadVideoSource(lazyVideo);
    } else {
        var sources = lazyVideo.getElementsByTagName("source");
        for (var j = 0; j < sources.length; j++) {
            var source = sources[j];
            lazyloadVideoSource(source);
        }
    }
}

function lazyloadVideoSource(source) {
    var src = source.getAttribute("data-src") ? source.getAttribute("data-src") : source.src;
    var srcset = source.getAttribute("data-srcset") ? source.getAttribute("data-srcset") : "";
    if (source.srcset != null & source.srcset != "") {
        source.srcset = srcset;
    }
    if (typeof(source.getElementsByTagName("source")[0]) == "undefined") {
        if (source.tagName == "SOURCE") {
            source.src = src;
            source.parentNode.load();
            if (source.parentNode.getAttribute("autoplay") !== null) {
                source.parentNode.play();
            }
        } else {
            source.src = src;
            source.load();
            if (source.getAttribute("autoplay") !== null) {
                source.play();
            }
        }
    } else {
        source.parentNode.src = src;
    }
    delete source.dataset.class;
}

function convert_to_video_tag(imgs) {
    const t = imgs.length > 0 ? imgs[0] : "";
    if (t) {
        delete imgs[0];
        var newelem = document.createElement("video");
        var index;
        for (index = t.attributes.length - 1; index >= 0; --index) {
            newelem.attributes.setNamedItem(t.attributes[index].cloneNode());
        }
        newelem.innerHTML = t.innerHTML;
        t.parentNode.replaceChild(newelem, t);
        if (typeof(newelem.getAttribute("data-poster")) == "string") {
            newelem.setAttribute("poster", newelem.getAttribute("data-poster"));
        }
        convert_to_video_tag(imgs);
    }
}

function lazyload_video(imgs, bodyRect, top, window_height, win_width) {
    for (var i = 0; i < imgs.length; i++) {
        var elem = imgs[i],
            elemRect = imgs[i].getBoundingClientRect();
        if (elemRect.top != 0 && (elemRect.top - (window_height - bodyRect.top)) < w3_lazy_load_by_px) {
            if (typeof(imgs[i].getElementsByTagName("source")[0]) == "undefined") {
                lazyload_video_source(imgs[i], top, window_height, win_width, elemRect, bodyRect);
            } else {
                var sources = imgs[i].getElementsByTagName("source");
                for (var j = 0; j < sources.length; j++) {
                    var source = sources[j];
                    lazyload_video_source(source, top, window_height, win_width, elemRect, bodyRect);
                }
            }
        }
    }
}