// utils/drive.ts
export function extractDriveId(url: string): string | null {
    // match id=... atau file/d/.../view
    const idParam = url.match(/[?&]id=([-\w]{10,})/i)?.[1];
    if (idParam) return idParam;
    const filePath = url.match(/\/file\/d\/([-\w]{10,})/i)?.[1];
    if (filePath) return filePath;
    const plainId = url.match(/[-\w]{25,}/)?.[0];
    return plainId ?? null;
}

export function driveSrcCandidates(fileId: string) {
    return [
        `https://drive.google.com/uc?export=view&id=${fileId}`,        // utama
        `https://drive.google.com/thumbnail?id=${fileId}&sz=w1000`,    // fallback 1
        `https://drive.google.com/uc?export=download&id=${fileId}`,    // fallback 2
    ];
}
