// components/DriveImage.tsx
import { useMemo, useState, useCallback } from "react";
import { extractDriveId, driveSrcCandidates } from "@/utils/drive";

type Props = React.ImgHTMLAttributes<HTMLImageElement> & {
    driveUrl: string;
    placeholder?: string;
};

export default function DriveImage({ driveUrl, placeholder = "/placeholder.jpg", alt, ...rest }: Props) {
    const candidates = useMemo(() => {
        const id = extractDriveId(driveUrl);
        return id ? driveSrcCandidates(id) : [];
    }, [driveUrl]);

    const [idx, setIdx] = useState(0);
    const currentSrc = candidates[idx] ?? placeholder;

    const handleError = useCallback(() => {
        setIdx((prev) => (prev + 1 < candidates.length ? prev + 1 : candidates.length)); // setelah habis → pakai placeholder
    }, [candidates.length]);

    return (
        <img
            src={currentSrc}
            alt={alt ?? "drive-image"}
            loading="lazy"
            decoding="async"
            referrerPolicy="no-referrer" // sering bantu untuk preview Drive
            onError={handleError}
            {...rest}
        />
    );
}
