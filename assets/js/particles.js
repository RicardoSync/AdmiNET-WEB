const canvas = document.getElementById("fondo-particulas");
const ctx = canvas.getContext("2d");

let w = (canvas.width = window.innerWidth);
let h = (canvas.height = window.innerHeight);

const numParticles = 90;
const maxDistance = 130;

let particles = [];

for (let i = 0; i < numParticles; i++) {
  particles.push({
    x: Math.random() * w,
    y: Math.random() * h,
    vx: (Math.random() - 0.5) * 1.5,
    vy: (Math.random() - 0.5) * 1.5,
    radius: Math.random() * 2 + 1,
  });
}

function draw() {
  ctx.clearRect(0, 0, w, h);

  // Dibujar partículas
  for (let i = 0; i < numParticles; i++) {
    const p = particles[i];
    p.x += p.vx;
    p.y += p.vy;

    // Rebote en bordes
    if (p.x < 0 || p.x > w) p.vx *= -1;
    if (p.y < 0 || p.y > h) p.vy *= -1;

    ctx.beginPath();
    ctx.arc(p.x, p.y, p.radius, 0, Math.PI * 2);
    ctx.fillStyle = "#00ccff";
    ctx.fill();
  }

  // Dibujar líneas entre partículas cercanas
  for (let i = 0; i < numParticles; i++) {
    for (let j = i + 1; j < numParticles; j++) {
      const dx = particles[i].x - particles[j].x;
      const dy = particles[i].y - particles[j].y;
      const dist = Math.sqrt(dx * dx + dy * dy);

      if (dist < maxDistance) {
        ctx.beginPath();
        ctx.moveTo(particles[i].x, particles[i].y);
        ctx.lineTo(particles[j].x, particles[j].y);
        ctx.strokeStyle = "rgba(0, 204, 255, " + (1 - dist / maxDistance) + ")";
        ctx.lineWidth = 0.7;
        ctx.stroke();
      }
    }
  }

  requestAnimationFrame(draw);
}

draw();

window.onresize = () => {
  w = canvas.width = window.innerWidth;
  h = canvas.height = window.innerHeight;
};
