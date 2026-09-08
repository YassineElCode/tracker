import React, { useEffect, useState, useRef } from 'react';
  import { Head, Link } from '@inertiajs/react';
  import { Navbar } from '@/components/navbar';

  interface Particle {
      id: number;
      x: number;
      y: number;
      size: number;
      color: string;
      speed: number;
      angle: number;
      spin: number;
      opacity: number;
  }

  const COLORS = ['#818cf8', '#a78bfa', '#c084fc', '#e879f9', '#f472b6', '#60a5fa', '#34d399'];

  const Confetti = () => {
      const canvasRef = useRef<HTMLCanvasElement>(null);
      const particlesRef = useRef<Particle[]>([]);
      const animFrameRef = useRef<number>(0);

      useEffect(() => {
          const canvas = canvasRef.current;
          if (!canvas) return;
          const ctx = canvas.getContext('2d');
          if (!ctx) return;

          canvas.width = window.innerWidth;
          canvas.height = window.innerHeight;

          // Create burst of particles
          for (let i = 0; i < 120; i++) {
              const angle = (Math.PI * 2 * i) / 120 + (Math.random() - 0.5) * 0.5;
              particlesRef.current.push({
                  id: i,
                  x: canvas.width / 2,
                  y: canvas.height / 2,
                  size: Math.random() * 8 + 4,
                  color: COLORS[Math.floor(Math.random() * COLORS.length)],
                  speed: Math.random() * 8 + 3,
                  angle,
                  spin: (Math.random() - 0.5) * 0.2,
                  opacity: 1,
              });
          }

          const animate = () => {
              ctx.clearRect(0, 0, canvas.width, canvas.height);
              particlesRef.current.forEach((p) => {
                  p.x += Math.cos(p.angle) * p.speed;
                  p.y += Math.sin(p.angle) * p.speed + 1.5; // gravity
                  p.speed *= 0.98;
                  p.angle += p.spin;
                  p.opacity -= 0.008;

                  if (p.opacity > 0) {
                      ctx.save();
                      ctx.globalAlpha = p.opacity;
                      ctx.fillStyle = p.color;
                      ctx.translate(p.x, p.y);
                      ctx.rotate(p.angle);
                      ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                      ctx.restore();
                  }
              });

              particlesRef.current = particlesRef.current.filter((p) => p.opacity > 0);
              if (particlesRef.current.length > 0) {
                  animFrameRef.current = requestAnimationFrame(animate);
              }
          };

          animFrameRef.current = requestAnimationFrame(animate);

          const handleResize = () => {
              canvas.width = window.innerWidth;
              canvas.height = window.innerHeight;
          };
          window.addEventListener('resize', handleResize);

          return () => {
              cancelAnimationFrame(animFrameRef.current);
              window.removeEventListener('resize', handleResize);
          };
      }, []);

      return (
          <canvas
              ref={canvasRef}
              className="fixed inset-0 z-10 pointer-events-none"
          />
      );
  };

  const DankuPage = () => {
      const [show, setShow] = useState(false);
      const [showSubtext, setShowSubtext] = useState(false);
      const [showButton, setShowButton] = useState(false);

      useEffect(() => {
          const t1 = setTimeout(() => setShow(true), 300);
          const t2 = setTimeout(() => setShowSubtext(true), 1000);
          const t3 = setTimeout(() => setShowButton(true), 1600);
          return () => { clearTimeout(t1); clearTimeout(t2); clearTimeout(t3); };
      }, []);

      return (
          <>
              <Head title="Dankjewel!" />
              <div className="min-h-screen font-sans antialiased text-gray-200 bg-gradient-to-br from-black via-gray-900 to-purple-900">
                  <Navbar />
                  <Confetti />
                  <main className="relative z-20 flex items-center justify-center min-h-screen px-4 pt-20">
                      <div className="text-center">
                          {/* Animated checkmark */}
                          <div
                              className={`mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-full
  bg-indigo-600/20 border-2 border-indigo-500/50 backdrop-blur-sm transition-all duration-700 ${
                                  show ? 'scale-100 opacity-100' : 'scale-0 opacity-0'
                              }`}
                          >
                              <svg
                                  className={`h-12 w-12 text-indigo-400 transition-all duration-500 delay-300 ${
                                      show ? 'scale-100 opacity-100' : 'scale-0 opacity-0'
                                  }`}
                                  fill="none"
                                  viewBox="0 0 24 24"
                                  stroke="currentColor"
                                  strokeWidth={2.5}
                              >
                                  <path
                                      strokeLinecap="round"
                                      strokeLinejoin="round"
                                      d="M5 13l4 4L19 7"
                                      className={show ? 'animate-draw-check' : ''}
                                      style={{
                                          strokeDasharray: 30,
                                          strokeDashoffset: show ? 0 : 30,
                                          transition: 'stroke-dashoffset 0.6s ease 0.5s',
                                      }}
                                  />
                              </svg>
                          </div>

                          {/* Main heading */}
                          <h1
                              className={`text-5xl md:text-7xl font-extrabold bg-gradient-to-r from-indigo-400
  via-purple-400 to-pink-400 bg-clip-text text-transparent transition-all duration-700 ${
                                  show ? 'translate-y-0 opacity-100' : 'translate-y-8 opacity-0'
                              }`}
                          >
                              Dankjewel!
                          </h1>

                          {/* Subtext */}
                          <p
                              className={`mt-6 text-xl md:text-2xl text-gray-300 max-w-lg mx-auto transition-all
  duration-700 ${
                                  showSubtext ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'
                              }`}
                          >
                              Je bericht is verstuurd. We nemen zo snel mogelijk contact met je op!
                          </p>

                          <p
                              className={`mt-3 text-gray-500 transition-all duration-700 delay-200 ${
                                  showSubtext ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'
                              }`}
                          >
                              Check ook je spam-map, voor de zekerheid.
                          </p>

                          {/* Back button */}
                          <div
                              className={`mt-10 transition-all duration-700 ${
                                  showButton ? 'translate-y-0 opacity-100' : 'translate-y-6 opacity-0'
                              }`}
                          >
                              <Link
                                  href="/"
                                  className="inline-flex items-center gap-2 px-8 py-3 text-lg font-semibold text-white transition-all duration-200 bg-indigo-600 rounded-lg shadow-lg hover:bg-indigo-700 hover:shadow-indigo-500/25 hover:scale-105"
                              >
                                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
  strokeWidth={2}>
                                      <path strokeLinecap="round" strokeLinejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"
   />
                                  </svg>
                                  Terug naar home
                              </Link>
                          </div>
                      </div>
                  </main>
              </div>
          </>
      );
  };

  export default DankuPage;