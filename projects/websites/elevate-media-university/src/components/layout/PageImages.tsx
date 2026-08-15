"use client";

import { useState, useEffect, useCallback } from "react";

export function PageImages({ images, captions }: { images: string[]; captions?: string[] }) {
  const [index, setIndex] = useState(0);

  const next = useCallback(() => setIndex((i) => (i + 1) % images.length), [images.length]);

  useEffect(() => {
    if (images.length < 2) return;
    const timer = setInterval(next, 4500);
    return () => clearInterval(timer);
  }, [images.length, next]);

  if (!images.length) return null;

  const src = images[index];

  return (
    <div className="mb-12">
      <div className="relative overflow-hidden rounded-2xl shadow-xl h-64 md:h-96">
        <img
          key={src}
          src={src}
          alt={captions?.[index] ?? ""}
          className="absolute inset-0 h-full w-full object-cover animate-fade-in"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent" />
        {captions?.[index] && (
          <p className="absolute bottom-0 left-0 right-0 px-5 py-4 text-sm font-medium text-white">
            {captions[index]}
          </p>
        )}
      </div>
      {images.length > 1 && (
        <div className="flex justify-center gap-2 mt-3">
          {images.map((_, i) => (
            <button
              key={i}
              onClick={() => setIndex(i)}
              aria-label={`Slide ${i + 1}`}
              className={`h-2.5 rounded-full transition-all ${
                i === index ? "w-8 bg-accent" : "w-2.5 bg-muted hover:bg-primary/40"
              }`}
            />
          ))}
        </div>
      )}
    </div>
  );
}

