'use client'

import { makeStyles, mergeClasses, tokens } from '@fluentui/react-components'
import {
  Home24Regular,
  Timeline24Regular,
  Building24Regular,
  People24Regular,
  Document24Regular,
  SignOut24Regular,
} from '@fluentui/react-icons'
import Link from 'next/link'
import { usePathname } from 'next/navigation'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    flexDirection: 'column',
    width: '280px',
    backgroundColor: '#2F6B3C',
    color: tokens.colorNeutralForegroundOnBrand,
    paddingTop: tokens.spacingVerticalM,
    paddingBottom: tokens.spacingVerticalM,
    paddingLeft: tokens.spacingHorizontalM,
    paddingRight: tokens.spacingHorizontalM,
    transition: 'all 0.3s ease',
    overflowY: 'auto',
    '@media (max-width: 768px)': {
      position: 'fixed',
      height: '100vh',
      zIndex: 100,
      transform: 'translateX(-100%)',
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.15)',
    },
  },
  rootOpen: {
    '@media (max-width: 768px)': {
      transform: 'translateX(0)',
    },
  },
  logo: {
    fontSize: tokens.fontSizeBase500,
    fontWeight: 700,
    marginBottom: tokens.spacingVerticalXL,
    display: 'flex',
    alignItems: 'center',
    gap: tokens.spacingHorizontalM,
    paddingBottom: tokens.spacingVerticalL,
    borderBottom: 'rgba(255, 255, 255, 0.15) solid 1px',
  },
  section: {
    marginBottom: tokens.spacingVerticalXL,
  },
  sectionTitle: {
    fontSize: tokens.fontSizeBase200,
    fontWeight: 600,
    marginBottom: tokens.spacingVerticalS,
    opacity: 0.7,
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
  },
  userInfo: {
    fontSize: tokens.fontSizeBase200,
    marginBottom: tokens.spacingVerticalL,
    paddingTop: tokens.spacingVerticalL,
    paddingBottom: tokens.spacingVerticalL,
    paddingLeft: tokens.spacingHorizontalM,
    paddingRight: tokens.spacingHorizontalM,
    borderBottom: `1px solid rgba(255, 255, 255, 0.15)`,
    borderRadius: tokens.borderRadiusMedium,
    backgroundColor: 'rgba(255, 255, 255, 0.08)',
  },
  userName: {
    fontWeight: 600,
    marginBottom: tokens.spacingVerticalXS,
  },
  userRole: {
    fontSize: tokens.fontSizeBase100,
    opacity: 0.8,
  },
  navItem: {
    display: 'flex',
    alignItems: 'center',
    gap: tokens.spacingHorizontalM,
    paddingTop: tokens.spacingVerticalM,
    paddingBottom: tokens.spacingVerticalM,
    paddingLeft: tokens.spacingHorizontalM,
    paddingRight: tokens.spacingHorizontalM,
    borderRadius: '10px',
    cursor: 'pointer',
    fontSize: tokens.fontSizeBase200,
    transition: 'all 0.25s ease',
    border: 'none',
    background: 'transparent',
    color: 'inherit',
    width: '100%',
    textAlign: 'left',
    marginBottom: tokens.spacingVerticalS,
    borderLeft: '3px solid transparent',
    '&:hover': {
      backgroundColor: 'rgba(255, 255, 255, 0.12)',
    },
  },
  navItemActive: {
    backgroundColor: 'rgba(102, 187, 106, 0.2)',
    fontWeight: 600,
    borderLeft: '3px solid #66BB6A',
    color: '#FFFFFF',
  },
  divider: {
    height: '1px',
    backgroundColor: 'rgba(255, 255, 255, 0.2)',
    marginTop: tokens.spacingVerticalL,
    marginBottom: tokens.spacingVerticalL,
  },
  flex: {
    flex: 1,
  },
})

interface SidebarProps {
  open: boolean
  onToggle: () => void
}

export function Sidebar({ open }: SidebarProps) {
  const styles = useStyles()
  const pathname = usePathname()

  return (
    <div className={mergeClasses(styles.root, open && styles.rootOpen)}>
      <div className={styles.logo}>
        <Building24Regular />
        <span>D&G Construction</span>
      </div>

      <div className={styles.userInfo}>
        <div className={styles.userName}>John Mitchell</div>
        <div className={styles.userRole}>Site Supervisor</div>
      </div>

      <div className={styles.section}>
        <div className={styles.sectionTitle}>Navigation</div>
        <Link href="/" className={mergeClasses(styles.navItem, pathname === '/' && styles.navItemActive)}>
          <Home24Regular />
          <span>Dashboard</span>
        </Link>
        <Link href="/timeline" className={mergeClasses(styles.navItem, pathname === '/timeline' && styles.navItemActive)}>
          <Timeline24Regular />
          <span>Project Timeline</span>
        </Link>
        <Link href="/phases" className={mergeClasses(styles.navItem, pathname === '/phases' && styles.navItemActive)}>
          <Building24Regular />
          <span>Construction Phases</span>
        </Link>
        <Link href="/attendance" className={mergeClasses(styles.navItem, pathname === '/attendance' && styles.navItemActive)}>
          <People24Regular />
          <span>Attendance</span>
        </Link>
        <Link href="/reports" className={mergeClasses(styles.navItem, pathname === '/reports' && styles.navItemActive)}>
          <Document24Regular />
          <span>Accomplishment Reports</span>
        </Link>
      </div>

      <div className={styles.flex} />

      <div className={styles.divider} />

      <div className={styles.section}>
        <button className={styles.navItem}>
          <People24Regular />
          <span>Profile</span>
        </button>
        <button className={styles.navItem}>
          <SignOut24Regular />
          <span>Logout</span>
        </button>
      </div>
    </div>
  )
}
