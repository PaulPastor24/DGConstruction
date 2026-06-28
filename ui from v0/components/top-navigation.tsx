'use client'

import { makeStyles, mergeClasses, tokens, Body2 } from '@fluentui/react-components'
import { ChevronLeft24Regular, ChevronRight24Regular, Person24Regular } from '@fluentui/react-icons'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingTop: '18px',
    paddingBottom: '18px',
    paddingLeft: tokens.spacingHorizontalL,
    paddingRight: tokens.spacingHorizontalL,
    backgroundColor: '#FFFFFF',
    borderBottom: '1px solid rgba(0, 0, 0, 0.08)',
    position: 'sticky',
    top: 0,
    zIndex: 50,
    boxShadow: 'none',
  },
  left: {
    display: 'flex',
    alignItems: 'center',
    gap: tokens.spacingHorizontalL,
  },
  menuButton: {
    display: 'none',
    background: 'transparent',
    border: 'none',
    cursor: 'pointer',
    color: tokens.colorNeutralForeground1,
    '@media (max-width: 768px)': {
      display: 'flex',
      alignItems: 'center',
    },
  },
  breadcrumb: {
    color: '#2F6B3C',
    fontSize: '15px',
    fontWeight: 500,
  },
  right: {
    display: 'flex',
    alignItems: 'center',
    gap: tokens.spacingHorizontalL,
  },
  iconButton: {
    background: 'transparent',
    border: 'none',
    cursor: 'pointer',
    color: '#2F6B3C',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: '40px',
    height: '40px',
    borderRadius: '50%',
    transition: 'all 0.25s ease',
    fontSize: '20px',
    '&:hover': {
      backgroundColor: 'rgba(47, 107, 60, 0.1)',
    },
  },
  date: {
    fontSize: '13px',
    color: '#666666',
    minWidth: '140px',
    textAlign: 'right',
    fontWeight: 500,
  },
})

interface TopNavigationProps {
  onMenuClick: () => void
  breadcrumb?: string
}

export function TopNavigation({ onMenuClick, breadcrumb = 'Dashboard / Overview' }: TopNavigationProps) {
  const styles = useStyles()
  const today = new Date()
  const dateStr = today.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' })

  return (
    <div className={styles.root}>
      <div className={styles.left}>
        <button className={styles.menuButton} onClick={onMenuClick}>
          ☰
        </button>
        <Body2 className={styles.breadcrumb}>{breadcrumb}</Body2>
      </div>

      <div className={styles.right}>
        <button className={styles.iconButton}>
          🔔
        </button>
        <button className={styles.iconButton}>
          <Person24Regular />
        </button>
        <div className={styles.date}>{dateStr}</div>
      </div>
    </div>
  )
}
