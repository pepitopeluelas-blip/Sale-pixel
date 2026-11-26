/* =========================================================
   SAFE PIXEL — Interacciones y animación 3D (Three.js)
   ========================================================= */

/* ⚙️ CONFIGURACIÓN: cambia aquí tu número de WhatsApp
   Formato: 52 + 10 dígitos, sin espacios ni signos. */
const CONFIG = {
  whatsapp: "5215500000000",
  whatsappVisible: "55 0000 0000",
  mensajeWhatsApp: "Hola Safe Pixel 👋, quiero cotizar una página web / sistema para mi negocio."
};

document.addEventListener("DOMContentLoaded", () => {
  initWhatsApp();
  initNav();
  initReveal();
  initCounters();
  initTyped();
  initTabs();
  initTilt();
  initForm();
  initHero3D();
  document.getElementById("year").textContent = new Date().getFullYear();
});

/* ---------- WhatsApp ---------- */
function initWhatsApp() {
  const url = `https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(CONFIG.mensajeWhatsApp)}`;
  document.querySelectorAll(".js-wa").forEach(a => {
    a.href = url;
    a.target = "_blank";
    a.rel = "noopener";
  });
  document.querySelectorAll(".js-wa-text").forEach(s => (s.textContent = CONFIG.whatsappVisible));
}

/* ---------- Navegación ---------- */
function initNav() {
  const nav = document.getElementById("nav");
  const toggle = document.getElementById("navToggle");
  const links = document.getElementById("navLinks");
  const onScroll = () => nav.classList.toggle("scrolled", window.scrollY > 40);
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  toggle.addEventListener("click", () => {
    const open = links.classList.toggle("open");
    toggle.setAttribute("aria-expanded", open);
  });
  links.querySelectorAll("a").forEach(a =>
    a.addEventListener("click", () => {
      links.classList.remove("open");
      toggle.setAttribute("aria-expanded", "false");
    })
  );
}

/* ---------- Aparición al hacer scroll ---------- */
function initReveal() {
  const io = new IntersectionObserver(
    entries => entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add("visible");
        io.unobserve(e.target);
      }
    }),
    { threshold: 0.12 }
  );
  document.querySelectorAll(".reveal").forEach((el, i) => {
    el.style.transitionDelay = `${(i % 4) * 80}ms`;
    io.observe(el);
  });
}

/* ---------- Contadores ---------- */
function initCounters() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target;
      const end = +el.dataset.count;
      const start = performance.now();
      const step = now => {
        const p = Math.min((now - start) / 1600, 1);
        el.textContent = Math.round(end * (1 - Math.pow(1 - p, 3)));
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
      io.unobserve(el);
    });
  });
  document.querySelectorAll("[data-count]").forEach(el => io.observe(el));
}

/* ---------- Texto que se escribe solo ---------- */
function initTyped() {
  const el = document.getElementById("typed");
  const words = ["pixel a pixel", "en Google", "en WhatsApp", "en todo celular"];
  let w = 0, c = words[0].length, deleting = true;
  const tick = () => {
    const word = words[w];
    c += deleting ? -1 : 1;
    el.textContent = word.slice(0, c);
    let delay = deleting ? 45 : 90;
    if (!deleting && c === word.length) { deleting = true; delay = 2200; }
    else if (deleting && c === 0) { deleting = false; w = (w + 1) % words.length; delay = 300; }
    setTimeout(tick, delay);
  };
  setTimeout(tick, 2500);
}

/* ---------- Pestañas de sistemas ---------- */
function initTabs() {
  const tabs = document.querySelectorAll(".tab");
  tabs.forEach(tab =>
    tab.addEventListener("click", () => {
      tabs.forEach(t => { t.classList.remove("active"); t.setAttribute("aria-selected", "false"); });
      document.querySelectorAll(".tab-panel").forEach(p => p.classList.remove("active"));
      tab.classList.add("active");
      tab.setAttribute("aria-selected", "true");
      document.getElementById(`tab-${tab.dataset.tab}`).classList.add("active");
    })
  );
}

/* ---------- Efecto 3D tilt en tarjetas ---------- */
function initTilt() {
  if (window.matchMedia("(hover: none)").matches) return;
  document.querySelectorAll(".tilt").forEach(card => {
    card.addEventListener("mousemove", e => {
      const r = card.getBoundingClientRect();
      const x = (e.clientX - r.left) / r.width;
      const y = (e.clientY - r.top) / r.height;
      card.style.transform = `perspective(900px) rotateX(${(0.5 - y) * 8}deg) rotateY(${(x - 0.5) * 10}deg) translateY(-4px)`;
      card.style.setProperty("--mx", `${x * 100}%`);
      card.style.setProperty("--my", `${y * 100}%`);
    });
    card.addEventListener("mouseleave", () => (card.style.transform = ""));
  });
}

/* ---------- Formulario de contacto ---------- */
function initForm() {
  const form = document.getElementById("contactForm");
  const msg = document.getElementById("formMsg");

  form.addEventListener("submit", async e => {
    e.preventDefault();
    msg.className = "form-msg";
    let valid = true;
    form.querySelectorAll("[required]").forEach(f => {
      const bad = !f.value.trim();
      f.classList.toggle("invalid", bad);
      if (bad) valid = false;
    });
    const email = form.email.value.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { form.email.classList.add("invalid"); valid = false; }
    if (!valid) { msg.textContent = "Revisa los campos marcados."; msg.classList.add("err"); return; }

    const btn = form.querySelector("button");
    btn.disabled = true;
    btn.textContent = "Enviando...";
    try {
      const res = await fetch(form.action, { method: "POST", body: new FormData(form) });
      const data = await res.json();
      if (!res.ok || !data.ok) throw new Error(data.error || "Error");
      msg.textContent = "¡Gracias! Te contactaremos muy pronto 🚀";
      msg.classList.add("ok");
      form.reset();
    } catch (err) {
      // Si el servidor no responde, se envía por WhatsApp como respaldo
      const texto = `Hola Safe Pixel, soy ${form.nombre.value}. Me interesa: ${form.servicio.value}. ${form.mensaje.value}`;
      msg.innerHTML = `No pudimos enviar el formulario. <a href="https://wa.me/${CONFIG.whatsapp}?text=${encodeURIComponent(texto)}" target="_blank" rel="noopener"><u>Envíalo por WhatsApp aquí</u></a>.`;
      msg.classList.add("err");
    } finally {
      btn.disabled = false;
      btn.textContent = "Enviar y cotizar gratis";
    }
  });
}

/* =========================================================
   ANIMACIÓN 3D DEL HERO — "Planeta de pixeles"
   Cientos de cubos forman una esfera que respira y reacciona
   al mouse, con un núcleo de alambre y un anillo de partículas.
   ========================================================= */
function initHero3D() {
  const canvas = document.getElementById("hero3d");
  if (!window.THREE || !canvas) return;
  const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));

  const scene = new THREE.Scene();
  scene.fog = new THREE.FogExp2(0x07070f, 0.045);
  const camera = new THREE.PerspectiveCamera(45, 1, 0.1, 100);
  camera.position.set(0, 0, 14);

  const world = new THREE.Group();
  scene.add(world);

  // Luces
  scene.add(new THREE.AmbientLight(0xffffff, 0.35));
  const l1 = new THREE.PointLight(0x00e5ff, 2.2, 40); l1.position.set(6, 4, 8); scene.add(l1);
  const l2 = new THREE.PointLight(0xff2bd6, 1.8, 40); l2.position.set(-6, -4, 6); scene.add(l2);

  // Esfera de pixeles (InstancedMesh = muy eficiente)
  const isMobile = window.innerWidth < 760;
  const COUNT = isMobile ? 420 : 900;
  const RADIUS = 3.6;
  const cubeGeo = new THREE.BoxGeometry(0.16, 0.16, 0.16);
  const cubeMat = new THREE.MeshStandardMaterial({ metalness: 0.4, roughness: 0.3, emissive: 0x1a0f40, emissiveIntensity: 0.6 });
  const cubes = new THREE.InstancedMesh(cubeGeo, cubeMat, COUNT);
  world.add(cubes);

  const base = [];
  const dummy = new THREE.Object3D();
  const color = new THREE.Color();
  const palette = [new THREE.Color(0x00e5ff), new THREE.Color(0x7c4dff), new THREE.Color(0xff2bd6)];
  const golden = Math.PI * (3 - Math.sqrt(5));
  for (let i = 0; i < COUNT; i++) {
    const y = 1 - (i / (COUNT - 1)) * 2;
    const r = Math.sqrt(1 - y * y);
    const th = golden * i;
    const v = new THREE.Vector3(Math.cos(th) * r, y, Math.sin(th) * r);
    base.push({ dir: v, phase: Math.random() * Math.PI * 2 });
    const t = (y + 1) / 2;
    color.copy(t < 0.5 ? palette[0] : palette[1]).lerp(t < 0.5 ? palette[1] : palette[2], (t % 0.5) * 2);
    cubes.setColorAt(i, color);
  }
  cubes.instanceColor.needsUpdate = true;

  // Núcleo de alambre
  const core = new THREE.Mesh(
    new THREE.IcosahedronGeometry(2.2, 1),
    new THREE.MeshBasicMaterial({ color: 0x7c4dff, wireframe: true, transparent: true, opacity: 0.35 })
  );
  world.add(core);

  // Anillo de partículas
  const ringCount = isMobile ? 600 : 1400;
  const ringPos = new Float32Array(ringCount * 3);
  for (let i = 0; i < ringCount; i++) {
    const a = Math.random() * Math.PI * 2;
    const d = 5 + Math.random() * 2.2;
    ringPos[i * 3] = Math.cos(a) * d;
    ringPos[i * 3 + 1] = (Math.random() - 0.5) * 0.4;
    ringPos[i * 3 + 2] = Math.sin(a) * d;
  }
  const ringGeo = new THREE.BufferGeometry();
  ringGeo.setAttribute("position", new THREE.BufferAttribute(ringPos, 3));
  const ring = new THREE.Points(ringGeo, new THREE.PointsMaterial({ color: 0x00e5ff, size: 0.04, transparent: true, opacity: 0.8 }));
  ring.rotation.x = 1.15;
  ring.rotation.z = 0.3;
  world.add(ring);

  // Polvo de estrellas de fondo
  const starCount = 800;
  const starPos = new Float32Array(starCount * 3);
  for (let i = 0; i < starCount * 3; i++) starPos[i] = (Math.random() - 0.5) * 60;
  const starGeo = new THREE.BufferGeometry();
  starGeo.setAttribute("position", new THREE.BufferAttribute(starPos, 3));
  const stars = new THREE.Points(starGeo, new THREE.PointsMaterial({ color: 0xffffff, size: 0.03, transparent: true, opacity: 0.5 }));
  scene.add(stars);

  // Interacción con el mouse / scroll
  const mouse = { x: 0, y: 0, tx: 0, ty: 0 };
  window.addEventListener("pointermove", e => {
    mouse.tx = (e.clientX / window.innerWidth - 0.5) * 2;
    mouse.ty = (e.clientY / window.innerHeight - 0.5) * 2;
  }, { passive: true });

  function resize() {
    const w = canvas.clientWidth, h = canvas.clientHeight;
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    const wide = w > 960;
    world.position.x = wide ? 4.2 : 0;
    world.position.y = wide ? 0 : 1.5;
    world.scale.setScalar(wide ? 1 : 0.8);
    canvas.style.opacity = wide ? 1 : 0.3;
  }
  window.addEventListener("resize", resize);
  resize();

  // Pausar cuando el hero no se ve (ahorra batería)
  let visible = true;
  new IntersectionObserver(([e]) => (visible = e.isIntersecting)).observe(canvas);

  const clock = new THREE.Clock();
  function animate() {
    requestAnimationFrame(animate);
    if (!visible) return;
    const t = reduced ? 0 : clock.getElapsedTime();

    mouse.x += (mouse.tx - mouse.x) * 0.05;
    mouse.y += (mouse.ty - mouse.y) * 0.05;

    for (let i = 0; i < COUNT; i++) {
      const b = base[i];
      const wave = Math.sin(t * 1.4 + b.phase) * 0.18 + Math.sin(b.dir.y * 6 + t * 2) * 0.22;
      const r = RADIUS + wave;
      dummy.position.copy(b.dir).multiplyScalar(r);
      dummy.lookAt(0, 0, 0);
      const s = 0.7 + (wave + 0.4) * 0.9;
      dummy.scale.set(s, s, s);
      dummy.updateMatrix();
      cubes.setMatrixAt(i, dummy.matrix);
    }
    cubes.instanceMatrix.needsUpdate = true;

    world.rotation.y = t * 0.15 + mouse.x * 0.5;
    world.rotation.x = mouse.y * 0.3;
    core.rotation.x = t * 0.3;
    core.rotation.y = -t * 0.2;
    ring.rotation.y = t * 0.08;
    stars.rotation.y = t * 0.01;
    camera.position.y = -window.scrollY * 0.004;

    renderer.render(scene, camera);
  }
  animate();
}
