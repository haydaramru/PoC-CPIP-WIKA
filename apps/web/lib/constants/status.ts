export type IngestionStatus =
  | "success"
  | "partial"
  | "failed"
  | "pending"
  | "processing";

export interface StatusConfig {
  label: string;
  dot: string;
  text: string;
  bg: string;
}

export const STATUS_CONFIG: Record<IngestionStatus, StatusConfig> = {
  success: {
    label: "Success",
    dot: "bg-green-500",
    text: "text-green-700",
    bg: "bg-green-50",
  },
  partial: {
    label: "Partial",
    dot: "bg-yellow-500",
    text: "text-yellow-700",
    bg: "bg-yellow-50",
  },
  failed: {
    label: "Failed",
    dot: "bg-red-500",
    text: "text-red-700",
    bg: "bg-red-50",
  },
  pending: {
    label: "Pending",
    dot: "bg-gray-400",
    text: "text-gray-500",
    bg: "bg-gray-50",
  },
  processing: {
    label: "Processing",
    dot: "bg-blue-500",
    text: "text-blue-700",
    bg: "bg-blue-50",
  },
};
