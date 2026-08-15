import { NextResponse } from "next/server";
import { auth } from "@/lib/auth";
import { uploadFile, getPublicUrl, BUCKETS } from "@/lib/supabase";

export async function POST(req: Request) {
  try {
    const session = await auth();
    if (!session) return NextResponse.json({ error: "Unauthorized" }, { status: 401 });

    const formData = await req.formData();
    const file = formData.get("file") as File | null;
    const bucket = formData.get("bucket") as string;
    const folder = (formData.get("folder") as string) || "";

    if (!file || !bucket) {
      return NextResponse.json({ error: "File and bucket are required" }, { status: 400 });
    }

    const allowedBuckets = Object.values(BUCKETS);
    if (!(allowedBuckets as string[]).includes(bucket)) {
      return NextResponse.json({ error: "Invalid bucket" }, { status: 400 });
    }

    const timestamp = Date.now();
    const sanitizedName = file.name.replace(/[^a-zA-Z0-9.-]/g, "_");
    const path = `${folder ? folder + "/" : ""}${session.user!.id}/${timestamp}-${sanitizedName}`;

    const buffer = Buffer.from(await file.arrayBuffer());

    await uploadFile(bucket, path, buffer, file.type);

    const url = getPublicUrl(bucket, path);

    return NextResponse.json({
      url,
      path,
      fileName: file.name,
      fileType: file.type,
      fileSize: file.size,
    });
  } catch (error) {
    console.error("Error uploading file:", error);
    return NextResponse.json({ error: "Upload failed" }, { status: 500 });
  }
}
