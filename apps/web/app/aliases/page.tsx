import type { Metadata } from "next";
import ColumnAliasManager from "@/components/aliases/ColumnAliasManager";

export const metadata: Metadata = {
  title: "Column Aliases - CPIP",
};

export default function ColumnAliasesPage() {
  return <ColumnAliasManager />;
}
